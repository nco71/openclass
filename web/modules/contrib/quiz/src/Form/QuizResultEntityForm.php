<?php

namespace Drupal\quiz\Form;

use Drupal;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz\Entity\Quiz;
use Drupal\quiz\Entity\QuizResult;
use Drupal\quiz\Util\QuizUtil;
use function _quiz_get_quiz_name;

class QuizResultEntityForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   *
   * Add the questions in this result to the edit form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $quiz_result QuizResult */
    $quiz_result = $this->entity;
    if ($quiz_result->isNew()) {
      $quiz = $quiz_result->getQuiz();

      if ($quiz_result->findOldResult()) {
        $form['build_on_last'] = [
          '#title' => t('Keep answers from last attempt?'),
          '#type' => 'radios',
          '#options' => [
            'fresh' => t('No answers'),
            'correct' => t('Only correct answers'),
            'all' => t('All answers'),
          ],
          '#default_value' => $quiz->get('build_on_last')->getString(),
          '#description' => t('You can choose to keep previous answers or start a new attempt.'),
          '#access' => $quiz->get('build_on_last')->getString() != 'fresh',
        ];
      }
      $form = parent::buildForm($form, $form_state);
      $form['actions']['submit']['#value'] = t('Start @quiz', ['@quiz' => QuizUtil::getQuizName()]);
    }
    else {
      $form['question']['#tree'] = TRUE;
      $render_controller = Drupal::entityTypeManager()
        ->getViewBuilder('quiz_result_answer');
      foreach ($quiz_result->getLayout() as $layoutIdx => $qra) {
        $form['question'][$layoutIdx]['feedback'] = $render_controller->view($qra);
        $form['question'][$layoutIdx] += $qra->getReportForm();
      }

      $form = parent::buildForm($form, $form_state);
      $form['actions']['submit']['#value'] = t('Save score');
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * Additionally update the score and feedback of the questions in this result.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /* @var $quiz_result QuizResult */
    $quiz_result = $this->entity;
    if ($quiz_result->isNew()) {
      $quiz_result->build_on_last = $form_state->getValue('build_on_last');
    }
    else {
      $layout = $this->entity->getLayout();

      // Update questions.
      foreach ($form_state->getValue('question') as $layoutIdx => $question) {
        $qra = $layout[$layoutIdx];
        $qra->set('points_awarded', $question['score']);
        $qra->set('answer_feedback', $question['answer_feedback']);
        $qra->set('is_evaluated', 1);
        $qra->save();
      }

      // Finalize result.
      $quiz_result->finalize();

      // Notify the user if results got deleted as a result of him scoring an
      // answer.
      $quiz = \Drupal::entityTypeManager()
        ->getStorage('quiz')
        ->loadRevision($quiz_result->get('vid')->getString());
      $results_got_deleted = $quiz_result->maintainResults();
      $add = '';
      if ($quiz->get('keep_results')->getString() == Quiz::KEEP_BEST && $results_got_deleted) {
        $add = t('Note that this @quiz is set to only keep each users best answer.', ['@quiz' => QuizUtil::getQuizName()]);
      }
      \Drupal::messenger()->addMessage(t('The scoring data you provided has been saved.') . $add);
    }

    // Update the result.
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * Start the quiz result if necessary.
   */
  public function save(array $form, FormStateInterface $form_state) {
    $new = $this->entity->isNew();

    // Save the quiz result.
    parent::save($form, $form_state);

    if ($new) {
      // The user submitted a quiz result form to start a new attempt. Set the
      // quiz result in the session.
      /* @var $quiz_session \Drupal\quiz\Services\QuizSessionInterface */
      $quiz_session = \Drupal::service('quiz.session');
      $quiz_session->startQuiz($this->entity);
    }
  }

}
