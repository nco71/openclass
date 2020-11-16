<?php

namespace Drupal\quiz_multichoice\Plugin\quiz\QuizQuestion;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\quiz\Entity\QuizQuestion;
use Drupal\quiz\Entity\QuizResultAnswer;
use function check_markup;

/**
 * @QuizQuestion (
 *   id = "multichoice",
 *   label = @Translation("Multiple choice question"),
 *   handlers = {
 *     "response" = "\Drupal\quiz_multichoice\Plugin\quiz\QuizQuestion\MultichoiceResponse"
 *   }
 * )
 */
class MultichoiceQuestion extends QuizQuestion {

  function save() {
    // Before we save we forgive some possible user errors.
    // @todo fix for D8
    //$this->forgive();
    return parent::save();
  }

  /**
   * Forgive some possible logical flaws in the user input.
   */
  private function forgive() {
    $config = Drupal::config('quiz_multichoice.settings');
    if ($this->node->choice_multi == 1) {
      for ($i = 0; isset($this->node->alternatives[$i]); $i++) {
        $short = &$this->node->alternatives[$i];
        // If the scoring data doesn't make sense, use the data from the
        // "correct" checkbox to set the score data.
        if ($short['score_if_chosen'] == $short['score_if_not_chosen'] || !is_numeric($short['score_if_chosen']) || !is_numeric($short['score_if_not_chosen'])) {
          if (!empty($short['correct'])) {
            $short['score_if_chosen'] = 1;
            $short['score_if_not_chosen'] = 0;
          }
          else {
            if ($config->get('scoring') == 0) {
              $short['score_if_chosen'] = -1;
              $short['score_if_not_chosen'] = 0;
            }
            elseif ($config->get('scoring') == 1) {
              $short['score_if_chosen'] = 0;
              $short['score_if_not_chosen'] = 1;
            }
          }
        }
      }
    }
    else {
      // For questions with one, and only one, correct answer, there will be
      // no points awarded for alternatives not chosen.
      for ($i = 0; isset($this->node->alternatives[$i]); $i++) {
        $short = &$this->node->alternatives[$i];
        $short['score_if_not_chosen'] = 0;
        if (isset($short['correct']) && $short['correct'] == 1 && !_quiz_is_int($short['score_if_chosen'], 1)) {
          $short['score_if_chosen'] = 1;
        }
      }
    }
  }

  /**
   * Warn the user about possible user errors.
   */
  private function warn() {
    // Count the number of correct answers.
    $num_corrects = 0;
    for ($i = 0; isset($this->node->alternatives[$i]); $i++) {
      $alt = &$this->node->alternatives[$i];
      if ($alt['score_if_chosen'] > $alt['score_if_not_chosen']) {
        $num_corrects++;
      }
    }
    if ($num_corrects == 1 && $this->node->choice_multi == 1 || $num_corrects > 1 && $this->node->choice_multi == 0) {
      $link_options = [];
      if (isset($_GET['destination'])) {
        $link_options['query'] = ['destination' => $_GET['destination']];
      }
      $go_back = l(t('go back'), 'quiz/' . $this->node->nid . '/edit', $link_options);
      if ($num_corrects == 1) {
        Drupal::messenger()->addWarning(
          t("Your question allows multiple answers. Only one of the alternatives have been marked as correct. If this wasn't intended please !go_back and correct it.", ['!go_back' => $go_back]), 'warning');
      }
      else {
        Drupal::messenger()->addWarning(
          t("Your question doesn't allow multiple answers. More than one of the alternatives have been marked as correct. If this wasn't intended please !go_back and correct it.", ['!go_back' => $go_back]), 'warning');
      }
    }
  }

  /**
   * Implementation of validateNode().
   *
   * @see QuizQuestion::validateNode()
   */
  public function validateNode(array &$form) {
    if ($this->node->choice_multi == 0) {
      $found_one_correct = FALSE;
      for ($i = 0; (isset($this->node->alternatives[$i]) && is_array($this->node->alternatives[$i])); $i++) {
        $short = $this->node->alternatives[$i];
        if (drupal_strlen($this->checkMarkup($i, 'answer')) < 1) {
          continue;
        }
        if ($short['correct'] == 1) {
          if ($found_one_correct) {
            // We don't display an error message here since we allow
            // alternatives to be partially correct.
          }
          else {
            $found_one_correct = TRUE;
          }
        }
      }
      if (!$found_one_correct) {
        form_set_error('choice_multi', t('You have not marked any alternatives as correct. If there are no correct alternatives you should allow multiple answers.'));
      }
    }
    else {
      for ($i = 0; isset($this->node->alternatives[$i]); $i++) {
        $short = $this->node->alternatives[$i];
        if (strlen($this->checkMarkup($i, 'answer')) < 1) {
          continue;
        }
        if ($short['score_if_chosen'] < $short['score_if_not_chosen'] && $short['correct']) {
          form_set_error("alternatives][$i][score_if_not_chosen", t("The alternative is marked as correct, but gives more points if you don't select it."));
        }
        elseif ($short['score_if_chosen'] > $short['score_if_not_chosen'] && !$short['correct']) {
          form_set_error("alternatives][$i][score_if_chosen", t('The alternative is not marked as correct, but gives more points if you select it.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAnsweringForm(FormStateInterface $form_state, QuizResultAnswer $quizQuestionResultAnswer) {
    $element = parent::getAnsweringForm($form_state, $quizQuestionResultAnswer);

    foreach ($this->get('alternatives')->referencedEntities() as $alternative) {
      /* @var $alternative Paragraph */
      $uuid = $alternative->get('uuid')->getString();
      $alternatives[$uuid] = $alternative;
    }

    // Build options list.
    $element['user_answer'] = [
      '#type' => 'tableselect',
      '#header' => ['answer' => t('Answer')],
      '#js_select' => FALSE,
      '#multiple' => $this->get('choice_multi')->getString(),
    ];

    // @todo see https://www.drupal.org/project/drupal/issues/2986517
    // There is some way to label the elements.
    foreach ($alternatives as $uuid => $alternative) {
      $vid = $alternative->getRevisionId();
      $multichoice_answer = $alternative->get('multichoice_answer')->getValue()[0];
      $answer_markup = check_markup($multichoice_answer['value'], $multichoice_answer['format']);
      $element['user_answer']['#options'][$vid]['title']['data']['#title'] = $answer_markup;
      $element['user_answer']['#options'][$vid]['answer'] = $answer_markup;
    }

    if ($this->get('choice_random')->getString()) {
      // We save the choice order so that the order will be the same in the
      // answer report.
      $element['choice_order'] = [
        '#type' => 'hidden',
        '#value' => implode(',', $this->shuffle($element['user_answer']['#options'])),
      ];
    }

    if ($quizQuestionResultAnswer->isAnswered()) {
      $choices = $quizQuestionResultAnswer->getResponse();
      if ($this->get('choice_multi')->getString()) {
        foreach ($choices as $choice) {
          $element['user_answer']['#default_value'][$choice] = TRUE;
        }
      }
      else {
        $element['user_answer']['#default_value'] = reset($choices);
      }
    }

    return $element;
  }

  /**
   * Custom shuffle function.
   *
   * It keeps the array key - value relationship intact.
   *
   * @param array $array
   *
   * @return array
   */
  private function shuffle(array &$array) {
    $newArray = [];
    $toReturn = array_keys($array);
    shuffle($toReturn);
    foreach ($toReturn as $key) {
      $newArray[$key] = $array[$key];
    }
    $array = $newArray;
    return $toReturn;
  }

  /**
   * {@inheritdoc}
   */
  public function getMaximumScore() {
    if ($this->get('choice_boolean')->getString()) {
      // Simple scoring - can only be worth 1 point.
      return 1;
    }

    $maxes = [0];
    foreach ($this->get('alternatives')->referencedEntities() as $alternative) {
      // "Not chosen" could have a positive point amount.
      $maxes[] = max($alternative->get('multichoice_score_chosen')->getString(), $alternative->get('multichoice_score_not_chosen')->getString());
    }

    if ($this->get('choice_multi')->getString()) {
      // For multiple answers, return the maximum possible points of all
      // positively pointed answers.
      return array_sum($maxes);
    }
    else {
      // For a single answer, return the highest pointed amount.
      return max($maxes);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getAnsweringFormValidate(array &$element, FormStateInterface $form_state) {
    $mcq = $element['#quiz_result_answer']->getQuizQuestion();
    if (!$mcq->get('choice_multi')->getString() && empty($element['user_answer']['#value'])) {
      $form_state->setError($element, (t('You must provide an answer.')));
    }
    parent::getAnsweringFormValidate($element, $form_state);
  }

}
