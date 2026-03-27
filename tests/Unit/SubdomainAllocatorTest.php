<?php

namespace Tests\Unit;

use App\Services\SubdomainAllocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubdomainAllocatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_branded_camel_case_course_name_yields_first_brand_segment(): void
    {
        $slug = app(SubdomainAllocator::class)->allocate('BritanikaCourseflow.com', null);

        $this->assertSame('britanika', $slug);
    }

    public function test_dotted_host_uses_first_label(): void
    {
        $slug = app(SubdomainAllocator::class)->allocate('academy.courseflow.test', null);

        $this->assertSame('academy', $slug);
    }
}
