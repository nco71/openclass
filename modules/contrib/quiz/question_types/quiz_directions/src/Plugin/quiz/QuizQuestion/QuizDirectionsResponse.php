<?php

namespace Drupal\quiz_directions\Plugin\quiz\QuizQuestion;

use Drupal\quiz\Entity\QuizResultAnswer;

/**
 * Extension of QuizQuestionResponse.
 */
class QuizDirectionsResponse extends QuizResultAnswer {

  /**
   * Implementation of score().
   *
   * @see QuizQuestionResponse::score()
   */
  public function score(array $values) {
    // Set the score.
    $this->score = 0;
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function isCorrect() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    return TRUE;
  }

}
