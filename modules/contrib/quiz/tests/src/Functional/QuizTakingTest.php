<?php

namespace Drupal\Tests\quiz\Functional;

use Drupal\quiz\Util\QuizUtil;

/**
 * Test quiz taking behavior.
 *
 * @group Quiz
 */
class QuizTakingTest extends QuizTestBase {

  public static $modules = [
    'quiz_multichoice',
    'quiz_directions',
    'quiz_truefalse',
  ];

  /**
   * Test the quiz availability tests.
   */
  public function testQuizAvailability() {
    // Anonymous doesn't have 'access quiz' permissions, so login a user that
    // has that permission.
    $this->drupalLogin($this->user);
    $future = \Drupal::time()->getRequestTime() + 86400;
    $past = \Drupal::time()->getRequestTime() - 86400;

    // Within range.
    $quiz_node_open = $this->createQuiz([
      'quiz_date' => [
        'value' => date('Y-m-d\TH:i:s', $past),
        'end_value' => date('Y-m-d\TH:i:s', $future),
      ],
    ]);
    $this->drupalGet("quiz/{$quiz_node_open->id()}");
    $this->assertNoText(t('This @quiz is closed.', ['@quiz' => QuizUtil::getQuizName()]));
    $this->assertNoText(t('You are not allowed to take this @quiz.', ['@quiz' => QuizUtil::getQuizName()]));

    // Starts in the future.
    $quiz_node_future = $this->createQuiz([
      'quiz_date' => [
        'value' => date('Y-m-d\TH:i:s', $future),
        'end_value' => date('Y-m-d\TH:i:s', $future + 1),
      ],
    ]);
    $this->drupalGet("quiz/{$quiz_node_future->id()}");
    $this->assertText(t('This @quiz is not yet open.', ['@quiz' => QuizUtil::getQuizName()]));
    $this->assertNoText(t('You are not allowed to take this @quiz.', ['@quiz' => QuizUtil::getQuizName()]));
    $this->drupalGet("quiz/{$quiz_node_future->id()}/take");
    $this->assertText(t('This @quiz is not yet open.', ['@quiz' => QuizUtil::getQuizName()]));

    // Ends in the past.
    $quiz_node_past = $this->createQuiz([
      'quiz_date' => [
        'value' => date('Y-m-d\TH:i:s', $past),
        'end_value' => date('Y-m-d\TH:i:s', $past + 1),
      ],
    ]);
    $this->drupalGet("quiz/{$quiz_node_past->id()}");
    $this->assertText(t('This @quiz is closed.', ['@quiz' => QuizUtil::getQuizName()]));
    $this->assertNoText(t('You are not allowed to take this @quiz.', ['@quiz' => QuizUtil::getQuizName()]));
    $this->drupalGet("quiz/{$quiz_node_past->id()}/take");
    $this->assertText(t('This @quiz is closed.', ['@quiz' => QuizUtil::getQuizName()]));

    // Always available.
    $quiz = $this->createQuiz([]);
    $this->drupalGet("quiz/{$quiz->id()}");
    $this->assertNoText(t('This @quiz is closed.', ['@quiz' => QuizUtil::getQuizName()]));
    $this->assertNoText(t('You are not allowed to take this @quiz.', ['@quiz' => QuizUtil::getQuizName()]));
  }

  /**
   * Make sure questions cannot be viewed outside of quizzes.
   */
  public function testViewQuestionsOutsideQuiz() {
    $this->drupalLogin($this->admin);
    $quiz_node = $this->createQuiz();

    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question1, $quiz_node);

    $this->drupalLogin($this->user);
    $this->drupalGet("quiz-question/{$question1->id()}");
    $this->assertResponse(403);

    $user_with_privs = $this->drupalCreateUser([
      'view quiz_question',
      'access quiz',
    ]);
    $this->drupalLogin($user_with_privs);
    $this->drupalGet("quiz-question/{$question1->id()}");
    $this->assertResponse(200);
  }

  /**
   * Test allow/restrict changing of answers.
   */
  public function testChangeAnswer() {
    $quiz_node = $this->createQuiz([
      'review_options' => ['question' => ['score' => 'score']],
    ]);

    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question1, $quiz_node);
    $question2 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question2, $quiz_node);
    $question3 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question3, $quiz_node);

    // Answer incorrectly.
    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz_node->id()}/take");
    $this->drupalGet("quiz/{$quiz_node->id()}/take/1");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 0,
    ], t('Next'));
    $this->assertText('Score: 0 of 1');

    // Go back and correct the answer.
    $this->drupalGet("quiz/{$quiz_node->id()}/take/1");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 1,
    ], t('Next'));
    $this->assertText('Score: 1 of 1');

    // Go back and incorrect the answer.
    $this->drupalGet("quiz/{$quiz_node->id()}/take/1");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 0,
    ], t('Next'));
    $this->assertText('Score: 0 of 1');

    $quiz_node->set('allow_change', 0);
    $quiz_node->save();

    // Check that the answer cannot be changed.
    $this->drupalGet("quiz/{$quiz_node->id()}/take/1");
    $this->assertSession()->fieldDisabled('edit-question-1-answer-1');
    $this->drupalPostForm(NULL, [], t('Next'));
    $this->assertText('Score: 0 of 1');

    // Check allow change/blank behavior.
    $this->drupalGet("quiz/{$quiz_node->id()}/take/2");
    $this->drupalPostForm(NULL, [], t('Leave blank'));
    $this->drupalGet("quiz/{$quiz_node->id()}/take/2");
    $this->assertSession()->fieldDisabled('edit-question-2-answer-1');
    $quiz_node->set('allow_change_blank', 1);
    $quiz_node->save();
    $this->drupalGet("quiz/{$quiz_node->id()}/take/2");
    $this->assertSession()->fieldEnabled('edit-question-2-answer-1');
  }

  public function testQuizMaxAttempts() {
    $quiz_node = $this->createQuiz([
      'takes' => 2,
    ]);

    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question1, $quiz_node);
    $question2 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question2, $quiz_node);

    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz_node->id()}/take");
    $this->drupalGet("quiz/{$quiz_node->id()}/take/1");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 0,
    ], t('Next'));
    $this->drupalPostForm(NULL, [
      "question[{$question2->id()}][answer]" => 0,
    ], t('Finish'));

    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz_node->id()}");
    $this->assertText('You can only take this Quiz 2 times. You have taken it 1 time.');
    $this->drupalGet("quiz/{$quiz_node->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 0,
    ], t('Next'));

    // Make sure we can get back.
    $this->drupalGet("quiz/{$quiz_node->id()}");
    $this->assertNoText('You can only take this Quiz 2 times. You have taken it 1 time.');
    $this->drupalGet("quiz/{$quiz_node->id()}/take/2");
    $this->drupalPostForm(NULL, [
      "question[{$question2->id()}][answer]" => 0,
    ], t('Finish'));

    // No more attempts.
    $this->drupalGet("quiz/{$quiz_node->id()}");
    $this->assertText('You have already taken this Quiz 2 times. You may not take it again.');
  }

  /**
   * Test that a user can answer a skipped question.
   */
  public function testAnswerSkipped() {
    $this->drupalLogin($this->admin);
    $quiz_node = $this->createQuiz([
      'allow_skipping' => 1,
      'allow_jumping' => 1,
    ]);

    // 2 questions.
    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question1, $quiz_node);
    $question2 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question2, $quiz_node);

    $this->drupalLogin($this->user);

    // Leave a question blank.
    $this->drupalGet("quiz/{$quiz_node->id()}/take");
    $this->drupalGet("quiz/{$quiz_node->id()}/take/1");
    $this->drupalPostForm(NULL, [], t('Leave blank'));

    // Fill out the blank question.
    $this->drupalGet("quiz/{$quiz_node->id()}/take/1");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 1,
    ], t('Next'));
    $this->drupalPostForm(NULL, [
      "question[{$question2->id()}][answer]" => 1,
    ], t('Finish'));

    $this->assertText("Your score: 100%");
  }

  /**
   * Make sure a user can answer or skip an old question's revision.
   */
  public function testAnswerOnOldQuizRevisioning() {
    $this->drupalLogin($this->admin);

    $question1 = $this->createQuestion([
      'title' => 'Q 1',
      'body' => 'Q 1',
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $quiz_node = $this->linkQuestionToQuiz($question1);

    $question2 = $this->createQuestion([
      'title' => 'Q 2',
      'body' => 'Q 2',
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question2, $quiz_node);

    $question1->revision = TRUE;
    $question1->save();

    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz_node->id()}/take");

    // Leave a question blank.
    $this->drupalGet("quiz/{$quiz_node->id()}/take/1");
    $this->drupalPostForm(NULL, [], t('Leave blank'));

    // Submit the question.
    $this->drupalGet("quiz/{$quiz_node->id()}/take/1");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 1,
    ], t('Next'));
  }

  /**
   * Verify non gradable questions are excluded from counts.
   */
  public function testQuestionCount() {
    $quiz_node = $this->createQuiz([
      'review_options' => ['question' => ['score' => 'score']],
    ]);

    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question1, $quiz_node);
    $question2 = $this->createQuestion([
      'type' => 'directions',
    ]);
    $this->linkQuestionToQuiz($question2, $quiz_node);
    $question3 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question3, $quiz_node);
    $question4 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question4, $quiz_node);
    $question5 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question5, $quiz_node);

    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz_node->id()}");

    // @todo check the pager, this isn't reliable
    $this->assertText("4");
  }

  /**
   * Test the mark doubtful functionality.
   */
  public function testMarkDoubtful() {
    $this->drupalLogin($this->admin);
    $quiz_node = $this->createQuiz([
      'allow_skipping' => 1,
      'allow_jumping' => 1,
      'mark_doubtful' => 1,
    ]);

    // 2 questions.
    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question1, $quiz_node);
    $question2 = $this->createQuestion([
      'type' => 'directions',
    ]);
    $this->linkQuestionToQuiz($question2, $quiz_node);

    // Take the quiz.
    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz_node->id()}/take");

    // Ensure it is on truefalse.
    $this->drupalGet("quiz/{$quiz_node->id()}/take/1");
    $this->assertField("edit-question-{$question1->id()}-is-doubtful");

    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 1,
      "question[{$question1->id()}][is_doubtful]" => 1,
    ], t('Next'));
    // Go back and verify it was saved.
    $this->drupalGet("quiz/{$quiz_node->id()}/take/1");
    $this->assertFieldChecked("edit-question-{$question1->id()}-is-doubtful");

    // Ensure it is not on quiz directions.
    $this->drupalGet("quiz/{$quiz_node->id()}/take/2");
    $this->assertNoField("edit-question-{$question2->id()}-is-doubtful");
  }

}
