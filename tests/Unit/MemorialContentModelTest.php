<?php

namespace Tests\Unit;

use App\Models\MemorialContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemorialContentModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function memorial_content_has_correct_fillable_attributes()
    {
        $content = new MemorialContent();

        $expectedFillable = [
            'content_type',
            'title',
            'content',
        ];

        $this->assertEquals($expectedFillable, $content->getFillable());
    }

    /** @test */
    public function memorial_content_has_correct_casts()
    {
        $content = new MemorialContent();

        $expectedCasts = [
            'id' => 'int',
            'updated_at' => 'datetime',
        ];

        $this->assertEquals($expectedCasts, $content->getCasts());
    }

    /** @test */
    public function memorial_content_has_valid_content_types()
    {
        $validTypes = ['bio', 'memorial_name', 'memorial_dates', 'contact_info'];

        foreach ($validTypes as $type) {
            $content = MemorialContent::factory()->create(['content_type' => $type]);
            $this->assertEquals($type, $content->content_type);
        }
    }

    /** @test */
    public function memorial_content_scope_by_type_filters_by_content_type()
    {
        MemorialContent::factory()->create(['content_type' => 'bio']);
        MemorialContent::factory()->create(['content_type' => 'memorial_name']);
        MemorialContent::factory()->create(['content_type' => 'contact_info']);
        MemorialContent::factory()->create(['content_type' => 'memorial_dates']);

        $bioContent = MemorialContent::byType('bio')->get();
        $nameContent = MemorialContent::byType('memorial_name')->get();
        $contactContent = MemorialContent::byType('contact_info')->get();

        $this->assertCount(1, $bioContent);
        $this->assertCount(1, $nameContent);
        $this->assertCount(1, $contactContent);

        $this->assertEquals('bio', $bioContent->first()->content_type);
        $this->assertEquals('memorial_name', $nameContent->first()->content_type);
        $this->assertEquals('contact_info', $contactContent->first()->content_type);
    }

    /** @test */
    public function memorial_content_can_get_content_types_list()
    {
        $expectedTypes = [
            'bio' => 'Biography',
            'memorial_name' => 'Memorial Name',
            'memorial_dates' => 'Memorial Dates',
            'contact_info' => 'Contact Information',
        ];

        $this->assertEquals($expectedTypes, MemorialContent::getContentTypes());
    }

    /** @test */
    public function memorial_content_has_display_name_accessor()
    {
        $content = MemorialContent::factory()->create(['content_type' => 'bio']);
        $this->assertEquals('Biography', $content->content_type_display);

        $content = MemorialContent::factory()->create(['content_type' => 'memorial_name']);
        $this->assertEquals('Memorial Name', $content->content_type_display);
    }

    /** @test */
    public function memorial_content_can_find_by_type()
    {
        $bioContent = MemorialContent::factory()->create(['content_type' => 'bio']);
        $nameContent = MemorialContent::factory()->create(['content_type' => 'memorial_name']);

        $foundBio = MemorialContent::findByType('bio');
        $foundName = MemorialContent::findByType('memorial_name');

        $this->assertEquals($bioContent->id, $foundBio->id);
        $this->assertEquals($nameContent->id, $foundName->id);
    }

    /** @test */
    public function memorial_content_find_by_type_returns_null_when_not_found()
    {
        $result = MemorialContent::findByType('non_existent_type');
        $this->assertNull($result);
    }

    /** @test */
    public function memorial_content_can_be_created_with_minimal_data()
    {
        $content = MemorialContent::create([
            'content_type' => 'bio',
            'title' => null,
            'content' => null,
        ]);

        $this->assertInstanceOf(MemorialContent::class, $content);
        $this->assertEquals('bio', $content->content_type);
        $this->assertNull($content->title);
        $this->assertNull($content->content);
    }

    /** @test */
    public function memorial_content_enforces_unique_content_type_constraint()
    {
        // Create first content of type 'bio'
        MemorialContent::factory()->create(['content_type' => 'bio']);

        // Attempt to create second content of same type should fail
        $this->expectException(\Illuminate\Database\QueryException::class);

        MemorialContent::factory()->create(['content_type' => 'bio']);
    }

    /** @test */
    public function memorial_content_updates_timestamp_on_change()
    {
        $content = MemorialContent::factory()->create();
        $originalTimestamp = $content->updated_at;

        // Wait a moment to ensure timestamp difference
        sleep(1);

        $content->update(['content' => 'Updated content']);

        $this->assertNotEquals($originalTimestamp, $content->fresh()->updated_at);
    }

    /** @test */
    public function memorial_content_can_have_long_content()
    {
        $longContent = str_repeat('This is a very long biography content. ', 100);

        $content = MemorialContent::factory()->create([
            'content_type' => 'bio',
            'content' => $longContent,
        ]);

        $this->assertEquals($longContent, $content->content);
        $this->assertTrue(strlen($content->content) > 1000);
    }
}