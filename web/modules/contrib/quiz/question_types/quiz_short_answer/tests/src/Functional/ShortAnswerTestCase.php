<?php

namespace Drupal\Tests\quiz_short_answer\Functional;

use Drupal\quiz\Entity\QuizQuestion;
use Drupal\quiz_short_answer\Plugin\quiz\QuizQuestion\ShortAnswerQuestion;
use Drupal\Tests\quiz\Functional\QuizQuestionTestBase;

/**
 * @file
 * Unit tests for the short_answer Module.
 */

/**
 * Test class for short answer.
 *
 * @group Quiz
 */
class ShortAnswerTestCase extends QuizQuestionTestBase {

  protected $profile = 'standard';

  public static $modules = ['quiz_short_answer'];

  /**
   * Test creating a short answer question.
   */
  public function testCreateQuizQuestion($settings = []) {
    $question = QuizQuestion::create($settings + [
        'type' => 'short_answer',
        'title' => 'SA 1 title',
        'body' => 'SA 1 body text.',
      ]);
    $question->save();

    return $question;
  }

  /**
   * Test case insensitive graded questions.
   */
  public function testGradeAnswerInsensitive() {
    // Login as our privileged user.
    $this->drupalLogin($this->admin);

    $question = QuizQuestion::create([
      'type' => 'short_answer',
      'short_answer_evaluation' => ShortAnswerQuestion::ANSWER_INSENSITIVE_MATCH,
      'short_answer_correct' => 'the Zero One Infinity rule',
    ]);
    $question->save();
    $quiz = $this->linkQuestionToQuiz($question);

    // Test incorrect.
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question->id()}][answer]" => 'This is an incorrect answer.',
    ], t('Finish'));
    $this->assertText('Your score: 0%');

    // Test correct.
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question->id()}][answer]" => 'the Zero One Infinity rule',
    ], t('Finish'));
    $this->assertText('Your score: 100%');

    // Test correct.
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question->id()}][answer]" => 'the zero one Infinity rule',
    ], t('Finish'));
    $this->assertText('Your score: 100%');
  }

  /**
   * Test case sensitive graded questions.
   */
  public function testGradeAnswerSensitive() {
    // Login as our privileged user.
    $this->drupalLogin($this->admin);

    $quiz = $this->createQuiz([
      'review_options' => ['end' => ['score' => 'score']],
    ]);

    $question = QuizQuestion::create([
      'type' => 'short_answer',
      'short_answer_evaluation' => ShortAnswerQuestion::ANSWER_MATCH,
      'short_answer_correct' => 'the Zero One Infinity rule',
    ]);
    $question->save();
    $this->linkQuestionToQuiz($question, $quiz);

    // Test incorrect.
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question->id()}][answer]" => 'the zero one Infinity rule',
    ], t('Finish'));
    $this->assertText('Your score: 0%');

    // Test correct.
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question->id()}][answer]" => 'the Zero One Infinity rule',
    ], t('Finish'));
    $this->assertText('Your score: 100%');
  }

  /**
   * Test regex graded questions.
   */
  public function testGradeAnswerRegex() {
    $this->drupalLogin($this->admin);

    $quiz = $this->createQuiz();

    $question = QuizQuestion::create([
      'type' => 'short_answer',
      'short_answer_evaluation' => ShortAnswerQuestion::ANSWER_REGEX,
      'short_answer_correct' => '/Zero One Infinity/i',
    ]);
    $question->save();
    $this->linkQuestionToQuiz($question, $quiz);

    // Test incorrect.
    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question->id()}][answer]" => 'um some rule, I forget',
    ], t('Finish'));
    $this->assertText('Your score: 0%');

    // Test correct.
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question->id()}][answer]" => 'the answer is the zero one infinity rule',
    ], t('Finish'));
    $this->assertText('Your score: 100%');
  }

  /**
   * Test manually graded questions.
   *
   * Note: we use two questions here to make sure the grading form is handled
   * correctly.
   */
  public function testGradeAnswerManualFeedback() {
    $this->drupalLogin($this->admin);

    $quiz = $this->createQuiz();

    $question1 = QuizQuestion::create([
      'type' => 'short_answer',
      'short_answer_evaluation' => ShortAnswerQuestion::ANSWER_MANUAL,
      'short_answer_correct' => 'the Zero One Infinity rule',
    ]);
    $question1->save();
    $this->linkQuestionToQuiz($question1, $quiz);

    $question2 = QuizQuestion::create([
      'type' => 'short_answer',
      'short_answer_evaluation' => ShortAnswerQuestion::ANSWER_MANUAL,
      'short_answer_correct' => 'The number two is ridiculous and cannot exist',
    ]);
    $question2->save();
    $this->linkQuestionToQuiz($question2, $quiz);

    // Test correct.
    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 'the answer is the zero one infinity rule',
    ], t('Next'));
    $this->drupalPostForm(NULL, [
      "question[{$question2->id()}][answer]" => 'the number two really is ridiculous',
    ], t('Finish'));
    $this->assertText('Your score: 0%');
    // Strange behavior - extra spaces in HTML.
    //$this->assertText('Score ? of 10');
    $this->assertText('This answer has not yet been scored.');
    $this->assertNoFieldByName('question[1][score]');
    $this->assertNoFieldByName('question[2][score]');
    $this->assertNoFieldByName('question[1][answer_feedback][value]');
    $this->assertNoFieldByName('question[2][answer_feedback][value]');
    $this->assertNoRaw(t('Save score'));
    $url_of_result = $this->getUrl();

    // Test grading the question.
    $this->drupalLogin($this->admin);
    $this->drupalGet('admin/quiz/reports/unevaluated');
    $this->clickLink(t('Score'));
    $this->assertFieldByName('question[1][score]');
    $this->assertFieldByName('question[2][score]');
    $this->drupalPostForm(NULL, [
      "question[1][score]" => 2,
      "question[2][score]" => 3,
      "question[1][answer_feedback][value]" => 'Feedback for answer 1.',
      "question[2][answer_feedback][value]" => 'Feedback for answer 2.',
      "question[1][answer_feedback][format]" => 'basic_html',
      "question[2][answer_feedback][format]" => 'basic_html',
    ], t('Save score'));
    $this->assertText('The scoring data you provided has been saved.');

    // Test the score is visible to the user.
    $this->drupalLogin($this->user);
    $this->drupalGet($url_of_result);
    $this->assertText('You got 5 of 10 possible points.');
    $this->assertText('Your score: 50%');
    // Strange behavior - extra spaces in HTML.
    //$this->assertText('Score 2 of 5');
    //$this->assertText('Score 3 of 5');
    $this->assertText('Feedback for answer 1.');
    $this->assertText('Feedback for answer 2.');
  }

  /**
   * Test that the question response can be edited.
   */
  public function testEditQuestionResponse() {
    $this->drupalLogin($this->admin);

    // Create & link a question.
    $question1 = QuizQuestion::create([
      'type' => 'short_answer',
    ]);
    $question1->save();
    $quiz = $this->linkQuestionToQuiz($question1);

    $question2 = QuizQuestion::create([
      'type' => 'short_answer',
    ]);
    $question2->save();
    $this->linkQuestionToQuiz($question2, $quiz);

    // Login as non-admin.
    $this->drupalLogin($this->user);

    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalGet("quiz/{$quiz->id()}/take/1");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 'um some rule, I forget',
    ], t('Next'));
    $this->drupalGet("quiz/{$quiz->id()}/take/1");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 'um some rule, I forget',
    ], t('Next'));
  }

  public function getQuestionType() {
    return 'short_answer';
  }

  /**
   * Test that the question response can be exported.
   */
  public function testViews() {
    // Create & link a question.
    $question1 = $this->testCreateQuizQuestion();
    $quiz = $this->linkQuestionToQuiz($question1);

    // Login as non-admin.
    $this->drupalLogin($this->user);
    $this->drupalGet("quiz/{$quiz->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 'um some rule, I forget',
    ], t('Finish'));

    $this->drupalGet("quiz/{$quiz->id()}/quiz-result-export-test");
    $this->assertText('um some rule, I forget');
  }

}
