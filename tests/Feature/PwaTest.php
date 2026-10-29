<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_json_is_accessible_and_valid(): void
    {
        $response = $this->get('/manifest.json');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json; charset=utf-8')
            ->assertJsonPath('name', 'Macha — Micro-reading Sosial')
            ->assertJsonPath('short_name', 'Macha')
            ->assertJsonPath('display', 'standalone')
            ->assertJsonPath('start_url', '/?source=pwa')
            ->assertJsonPath('scope', '/');

        $icons = $response->json('icons');
        $this->assertNotEmpty($icons);

        $has192 = collect($icons)->contains(fn ($icon) => $icon['sizes'] === '192x192');
        $has512 = collect($icons)->contains(fn ($icon) => $icon['sizes'] === '512x512');
        $this->assertTrue($has192, 'Manifest should include 192x192 icon');
        $this->assertTrue($has512, 'Manifest should include 512x512 icon');
    }

    public function test_service_worker_script_is_accessible_with_root_scope_header(): void
    {
        $response = $this->get('/sw.js');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8')
            ->assertHeader('Service-Worker-Allowed', '/')
            ->assertSee('macha-cache-v1');
    }

    public function test_offline_page_is_accessible(): void
    {
        $response = $this->get('/offline.html');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=utf-8')
            ->assertSee('Koneksi Terputus')
            ->assertSee('Mode Offline');
    }

    public function test_pages_contain_pwa_meta_tags_and_manifest_link(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('rel="manifest"', false)
            ->assertSee('manifest.json')
            ->assertSee('name="theme-color" content="#102A2A"', false)
            ->assertSee('name="apple-mobile-web-app-capable" content="yes"', false)
            ->assertSee('apple-touch-icon');
    }
}
