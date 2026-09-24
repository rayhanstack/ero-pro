<?php

namespace Tests\Unit;

use App\Helpers\MediaHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_upload_stores_single_file_and_returns_payload(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg');

        $result = MediaHelper::upload($file, 'avatars', 'public');

        $this->assertIsArray($result);
        $this->assertEquals('public', $result['disk']);
        $this->assertNotEmpty($result['file']);

        Storage::disk('public')->assertExists($result['file']);
    }

    public function test_upload_stores_multiple_files(): void
    {
        $files = [
            UploadedFile::fake()->image('doc1.png'),
            UploadedFile::fake()->image('doc2.png'),
        ];

        $result = MediaHelper::upload($files, 'documents', 'public');

        $this->assertIsArray($result);
        $this->assertEquals('public', $result['disk']);
        $this->assertCount(2, $result['files']);

        foreach ($result['files'] as $filePath) {
            Storage::disk('public')->assertExists($filePath);
        }
    }

    public function test_delete_removes_file_from_storage(): void
    {
        $file = UploadedFile::fake()->image('delete_me.png');
        $uploaded = MediaHelper::upload($file, 'temp', 'public');

        Storage::disk('public')->assertExists($uploaded['file']);

        $deleted = MediaHelper::delete($uploaded);
        $this->assertTrue($deleted);

        Storage::disk('public')->assertMissing($uploaded['file']);
    }

    public function test_url_generates_correct_storage_or_fallback_url(): void
    {
        $fallbackUrl = MediaHelper::url(null, null, 'avatar');
        $this->assertStringContainsString('default.webp', $fallbackUrl);

        $file = UploadedFile::fake()->image('profile.jpg');
        $uploaded = MediaHelper::upload($file, 'profiles', 'public');

        $url = MediaHelper::url($uploaded);
        $this->assertStringContainsString('profile', $url);

        $directUrl = 'https://cdn.example.com/asset.jpg';
        $this->assertEquals($directUrl, MediaHelper::url($directUrl));
    }
}
