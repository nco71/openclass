<?php

namespace Drupal\Tests\quiz\Functional;

use Drupal\quiz\Entity\QuizResult;
use Drupal\quiz_short_answer\Plugin\quiz\QuizQuestion\ShortAnswerQuestion;

/**
 * Test quiz grading.
 *
 * @group Quiz
 */
class QuizGradingTest extends QuizTestBase {

  public static $modules = ['quiz_truefalse', 'quiz_short_answer'];

  /**
   * Test question weights.
   */
  public function testWeightedScore() {
    $this->drupalLogin($this->admin);

    $question1 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $question2 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $question3 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);

    // Link the questions. Make a 26 point quiz.
    $quiz_node = $this->linkQuestionToQuiz($question1);
    $this->linkQuestionToQuiz($question2, $quiz_node);
    $this->linkQuestionToQuiz($question3, $quiz_node);

    $this->drupalGet("quiz/{$quiz_node->id()}/questions");

    // drupalPostForm cannot post disabled fields.
    $this->drupalPostForm(NULL, [
      'question_list[1][auto_update_max_score]' => FALSE,
      'question_list[2][auto_update_max_score]' => FALSE,
      'question_list[3][auto_update_max_score]' => FALSE,
    ], t('Submit'));
    $this->drupalPostForm(NULL, [
      'question_list[1][max_score]' => 1,
      'question_list[2][max_score]' => 5,
      'question_list[3][max_score]' => 20,
    ], t('Submit'));

    // Login as non-admin.
    $this->drupalLogin($this->user);

    // Test correct question.
    $this->drupalGet("quiz/{$quiz_node->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 0,
    ], t('Next'));
    $this->drupalPostForm(NULL, [
      "question[{$question2->id()}][answer]" => 0,
    ], t('Next'));
    $this->drupalPostForm(NULL, [
      "question[{$question3->id()}][answer]" => 1,
    ], t('Finish'));
    $this->assertText('You got 20 of 26 possible points.');

    $quiz_result = QuizResult::load(1);
    $layout = $quiz_result->getLayout();

    // Make sure the values in the database are correct.
    $this->assertEqual($layout[1]->get('points_awarded')->getString(), 0);
    $this->assertEqual($layout[2]->get('points_awarded')->getString(), 0);
    $this->assertEqual($layout[3]->get('points_awarded')->getString(), 20);

    // Total score is 20/26.
    $this->assertEqual($quiz_result->get('score')->getString(), 77);
  }

  /**
   * Test question weights.
   */
  public function testManualWeightedScore() {
    $question1 = $this->createQuestion([
      'type' => 'short_answer',
      'short_answer_evaluation' => ShortAnswerQuestion::ANSWER_MANUAL,
      'short_answer_correct' => 'the Zero One Infinity rule',
    ]);
    $quiz_node = $this->linkQuestionToQuiz($question1);

    $question2 = $this->createQuestion([
      'type' => 'truefalse',
      'truefalse_correct' => 1,
    ]);
    $this->linkQuestionToQuiz($question2, $quiz_node);

    // Link the question. Make a 10 point quiz with a 7 point SA and 3 point TF.
    $this->drupalLogin($this->admin);
    $this->drupalGet("quiz/{$quiz_node->id()}/questions");
    $this->drupalPostForm(NULL, [
      'question_list[1][auto_update_max_score]' => FALSE,
      'question_list[2][auto_update_max_score]' => FALSE,
    ], t('Submit'));
    $this->drupalPostForm(NULL, [
      'question_list[1][max_score]' => 7,
      'question_list[2][max_score]' => 3,
    ], t('Submit'));

    // Login as non-admin.
    $this->drupalLogin($this->user);

    // Test correct question.
    $this->drupalGet("quiz/{$quiz_node->id()}/take");
    $this->drupalPostForm(NULL, [
      "question[{$question1->id()}][answer]" => 'blah blah',
    ], t('Next'));
    $this->drupalPostForm(NULL, [
      "question[{$question2->id()}][answer]" => 1,
    ], t('Finish'));
    $this->assertText('You got 3 of 10 possible points.');

    // Test grading the question.
    $this->drupalLogin($this->admin);
    $this->drupalGet("quiz/{$quiz_node->id()}/result/1/edit");

    $this->drupalPostForm(NULL, [
      "question[1][score]" => 3,
    ], t('Save score'));

    $quiz_result = QuizResult::load(1);
    $layout = $quiz_result->getLayout();

    $this->assertEqual($layout[1]->get('points_awarded')->getString(), 3);
    $this->assertEqual($layout[2]->get('points_awarded')->getString(), 3);

    // We got 3 + 3 points out of 10.
    // Unweighted we would have received 2.14 + 1 point.
    $this->assertEqual($quiz_result->get('score')->getString(), 60);
  }

}
