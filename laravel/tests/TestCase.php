<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::delete('delete from class_rooms');
        DB::delete('delete from users');
    }
    protected function isErrorSafety(\Illuminate\Testing\TestResponse $res, $errorStatus)
    {
        $res->assertStatus($errorStatus);
        $this->assertNotNull($res->json('error.message'));
        $this->assertNotNull($res->json('error.trace_id'));
    }
}
