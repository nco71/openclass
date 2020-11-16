<?php

namespace Drupal\quiz_page\Plugin\quiz\QuizQuestion;

use Drupal\Core\Form\FormStateInterface;
use Drupal\quiz\Entity\QuizQuestion;
use Drupal\quiz\Entity\QuizResultAnswer;

/**
 * @file
 * Quiz page classes.
 *
 * This module uses the question interface to define something which is
 * actually not a question.
 *
 * A Quiz page node is a placeholder for presenting multiple questions
 * on the same page.
 */

/**
 * @QuizQuestion (
 *   id = "page",
 *   label = @Translation("Quiz page"),
 *   handlers = {
 *     "response" = "\Drupal\quiz_page\Plugin\quiz\QuizQuestion\QuizPageResponse"
 *   }
 * )
 */
class QuizPageQuestion extends QuizQuestion {

  /**
   * Implementation of getAnsweringForm().
   *
   * @see QuizQuestion::getAnsweringForm()
   */
  public function getAnsweringForm(FormStateInterface $form_state, QuizResultAnswer $quizQuestionResultAnswer) {
    $element = [
      '#type' => 'hidden',
    ];
    return $element;
  }

  /**
   * Implementation of getCreationForm().
   *
   * @see QuizQuestion::getCreationForm()
   */
  public function getCreationForm(array &$form_state = NULL) {
    return [];
  }

  /**
   * Implementation of getMaximumScore().
   *
   * @see QuizQuestion::getMaximumScore()
   */
  public function getMaximumScore() {
    return 0;
  }

  /**
   * Implementation of isGraded().
   *
   * @see QuizQuestion::isGraded()
   */
  public function isGraded() {
    return FALSE;
  }

  /**
   * Implementation of hasFeedback().
   *
   * @see QuizQuestion::hasFeedback()
   */
  public function hasFeedback() {
    return FALSE;
  }

  /**
   * Implementation of isQuestion().
   *
   * @see QuizQuestion::hasFeedback()
   */
  public function isQuestion() {
    return FALSE;
  }

}
