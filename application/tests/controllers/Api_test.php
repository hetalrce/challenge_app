<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Api_test extends TestCase
{
  public function test_status_when_empty_array()
  {
    $output = $this->request('GET', ['Api', 'status_get']);

    $this->assertResponseCode(200);
    $this->assertContains('{"message":"No data found"}', $output);
  }

  public function test_status()
  {
    $output = $this->request(
      'GET',
      'api/status',
      ['id' => 1]
    );

    $this->assertResponseCode(200);
  }

  public function test_method_404()
  {
    $this->request('POST', 'api/status');

    $this->assertResponseCode(405);
  }

  public function test_APPPATH()
  {
    $actual = realpath(APPPATH);
    $expected = realpath(__DIR__ . '/../..');
    $this->assertEquals(
      $expected,
      $actual,
      'Your APPPATH seems to be wrong. Check your $application_folder in tests/Bootstrap.php'
    );
  }
}
