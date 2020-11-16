<?php

namespace Drupal\quiz_long_answer\Plugin\quiz\QuizQuestion;

use Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz\Entity\QuizQuestion;
use Drupal\quiz\Entity\QuizResultAnswer;
use function check_markup;
use function filter_default_format;

/**
 * @QuizQuestion (
 *   id = "long_answer",
 *   label = @Translation("Long answer question"),
 *   handlers = {
 *     "response" = "\Drupal\quiz_long_answer\Plugin\quiz\QuizQuestion\LongAnswerResponse"
 *   }
 * )
 */
class LongAnswerQuestion extends QuizQuestion {

  /**
   * Implementation of getNodeView().
   *
   * @see QuizQuestion::getNodeView()
   */
  public function getNodeView() {
    $content = parent::getNodeView();
    if ($this->viewCanRevealCorrect()) {
      $content['answers'] = [
        '#type' => 'item',
        '#title' => t('Rubric'),
        '#markup' => '<div class="quiz-solution">' . check_markup($this->node->rubric['value'], $this->node->rubric['format']) . '</div>',
        '#weight' => 1,
      ];
    }
    else {
      $content['answers'] = [
        '#markup' => '<div class="quiz-answer-hidden">Answer hidden</div>',
        '#weight' => 1,
      ];
    }
    return $content;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnsweringForm(FormStateInterface $form_state, QuizResultAnswer $quizQuestionResultAnswer) {
    $element = parent::getAnsweringForm($form_state, $quizQuestionResultAnswer);

    $element += [
      '#title' => t('Answer'),
      '#description' => t('Enter your answer here. If you need more space, click on the grey bar at the bottom of this area and drag it down.'),
      '#rows' => 15,
      '#cols' => 60,
    ];

    if ($this->get('answer_text_processing')->getString()) {
      $element['#type'] = 'text_format';
    }
    else {
      $element['#type'] = 'textarea';
    }

    if ($quizQuestionResultAnswer->isAnswered()) {
      $element['#default_value'] = $quizQuestionResultAnswer->getResponse();
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function getAnsweringFormValidate(array &$element, FormStateInterface $form_state) {
    parent::getAnsweringFormValidate($element, $form_state);

    if (isset($element['value'])) {
      $check = &$element['value'];
    }
    else {
      $check = &$element;
    }

    if (empty($check['#value'])) {
      $form_state->setError($check, t('You must provide an answer.'));
    }
  }

  /**
   * Implementation of getCreationForm().
   *
   * @see QuizQuestion::getCreationForm()
   */
  public function getCreationForm(array &$form_state = NULL) {
    $form = [];

    $form['rubric'] = [
      '#type' => 'text_format',
      '#title' => t('Rubric'),
      '#description' => t('Specify the criteria for grading the response.'),
      '#default_value' => isset($this->node->rubric['value']) ? $this->node->rubric['value'] : '',
      '#format' => isset($this->node->rubric['format']) ? $this->node->rubric['format'] : filter_default_format(),
      '#size' => 60,
      '#required' => FALSE,
    ];

    $form['answer_text_processing'] = [
      '#title' => t('Answer text processing'),
      '#description' => t('Allowing filtered text may enable the user to input HTML tags in their answer.'),
      '#type' => 'radios',
      '#options' => [
        0 => t('Plain text'),
        1 => t('Filtered text (user selects text format)'),
      ],
      '#default_value' => isset($this->node->answer_text_processing) ? $this->node->answer_text_processing : 0,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getMaximumScore() {
    return Drupal::config('quiz_long_answer.settings')
      ->get('default_max_score');
  }

}
