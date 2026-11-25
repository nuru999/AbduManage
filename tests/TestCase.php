<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Views call @vite; no compiled assets are needed to test behaviour.
        $this->withoutVite();
    }
    //
}
