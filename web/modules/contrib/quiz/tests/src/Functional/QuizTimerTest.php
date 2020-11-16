<?php

namespace Drupal\Tests\quiz\Functional;

/**
 * Test the quiz timer.
 *
 * @group Quiz
 */
class QuizTimerTest extends QuizTestBase {

  public static $modules = ['quiz_truefalse'];

  /**
   * Test quiz timer expiration.
   */
  public function testQuizTimer() {
    // Set up a quiz to show us feedback, 30 second expiration.
    $quiz = $this->createQuiz([
      'review_options' => ['end' => ['score' => 'score']],
      'time_limit' => 30,
    ]);

    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question1, $quiz);
    $question2 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question2, $quiz);
    $question3 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question3, $quiz);

    // Record 2 answers before expiration.
    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalGet("quiz/{$quiz->id()}/take/1");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 1,
    ], t('Next'));
    $this->assertNoText(t('The last answer was not submitted, as the time ran out.'));
    $this->drupalPostForm(NULL, [
      "question[{$question2->id()}][answer]" => 1,
    ], t('Next'));
    $this->assertNoText(t('The last answer was not submitted, as the time ran out.'));

    // Set the quiz result to have started 31 seconds ago.
    \Drupal::database()
      ->query('UPDATE {quiz_result} SET time_start = :time', [
        ':time' => \Drupal::time()
            ->getRequestTime() - 31,
      ]);
    \Drupal::entityTypeManager()->getStorage('quiz_result')->resetCache();

    // Submit the last question past the expiration.
    $this->drupalPostForm(NULL, [
      "question[{$question3->id()}][answer]" => 1,
    ], t('Finish'));
    $this->assertText(t('The last answer was not submitted, as the time ran out.'));
    $this->assertText('You got 2 of 3 possible points.');
  }

}
