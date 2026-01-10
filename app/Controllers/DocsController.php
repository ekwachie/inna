<?php

namespace app\Controllers;

use app\Core\Controller;
use app\Core\Request;
use app\Core\Response;
use app\Core\Application;
use app\Core\Utils\DUtil;

class DocsController extends Controller
{
    public function index(Request $request, Response $response)
    {
        // DUtil::debug("sssss");
        $docs = $this->getDocsStructure();
        
        return $this->render('docs/index', [
            'docs' => $docs,
            'content' => $this->getDocContent('README'),
            'currentDoc' => 'README'
        ]);
    }
    
    public function show(Request $request, Response $response)
    {
        $path = $request->getRouteParam('path', 'README');
        $docs = $this->getDocsStructure();
        
        // Convert path like "getting-started/installation" to file path
        $filePath = $this->resolveDocPath($path);
        
        if (!$filePath || !file_exists($filePath)) {
            return $this->render('docs/index', [
                'docs' => $docs,
                'content' => $this->getDocContent('README'),
                'currentDoc' => 'README',
                'error' => 'Documentation page not found'
            ]);
        }
        
        $content = $this->parseMarkdown(file_get_contents($filePath));
        
        return $this->render('docs/index', [
            'docs' => $docs,
            'content' => $content,
            'currentDoc' => $path,
            'title' => $this->getDocTitle($path)
        ]);
    }
    
    private function getDocsStructure()
    {
        $docsDir = $_SERVER['DOCUMENT_ROOT'] . '/docs';
        $structure = [];
        
        // Main README
        $structure['README'] = [
            'title' => 'Documentation',
            'path' => 'README',
            'children' => []
        ];
        
        // Scan docs directory
        if (is_dir($docsDir)) {
            $this->scanDocsDirectory($docsDir, $structure);
        }
        
        return $structure;
    }
    
    private function scanDocsDirectory($dir, &$structure, $prefix = '')
    {
        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $path = $dir . '/' . $item;
            $key = $prefix ? $prefix . '/' . pathinfo($item, PATHINFO_FILENAME) : pathinfo($item, PATHINFO_FILENAME);
            
            if (is_dir($path)) {
                $structure[$key] = [
                    'title' => $this->formatTitle($item),
                    'path' => $key,
                    'children' => []
                ];
                
                // Scan subdirectory
                $this->scanDocsDirectory($path, $structure[$key]['children'], $key);
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'md') {
                $filename = pathinfo($item, PATHINFO_FILENAME);
                $docKey = $prefix ? $prefix . '/' . $filename : $filename;
                
                if (!isset($structure[$docKey])) {
                    $structure[$docKey] = [
                        'title' => $this->formatTitle($filename),
                        'path' => $docKey,
                        'children' => []
                    ];
                }
            }
        }
    }
    
    private function resolveDocPath($path)
    {
        $docsDir = $_SERVER['DOCUMENT_ROOT'] . '/docs';
        
        if ($path === 'README') {
            return $docsDir . '/README.md';
        }
        
        $filePath = $docsDir . '/' . $path . '.md';
        
        if (file_exists($filePath)) {
            return $filePath;
        }
        
        return null;
    }
    
    private function getDocContent($path)
    {
        $filePath = $this->resolveDocPath($path);
        
        if ($filePath && file_exists($filePath)) {
            return $this->parseMarkdown(file_get_contents($filePath));
        }
        
        return '<p>Documentation not found.</p>';
    }
    
    private function parseMarkdown($content)
    {
        // Convert markdown links to internal docs links
        $content = preg_replace_callback('/\[([^\]]+)\]\(([^\)]+)\)/', function($matches) {
            $text = $matches[1];
            $url = $matches[2];
            
            // Convert relative markdown links to docs routes
            if (strpos($url, 'http') !== 0 && strpos($url, '#') !== 0) {
                // Remove .md extension if present
                $url = preg_replace('/\.md$/', '', $url);
                // Convert to docs route
                $url = '/documentation/' . ltrim($url, '/');
            }
            
            return '<a href="' . htmlspecialchars($url) . '" class="text-primary hover:text-secondary underline">' . htmlspecialchars($text) . '</a>';
        }, $content);
        
        // Code blocks (handle multiline)
        $content = preg_replace_callback('/```(\w+)?\n(.*?)```/s', function($matches) {
            $lang = $matches[1] ?? '';
            // Preserve newlines - htmlspecialchars keeps them, but ensure they're there
            $code = htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
            // Ensure newlines are preserved and use pre-wrap to handle long lines
            return '<pre style="white-space: pre; font-family: Monaco, Menlo, \'Ubuntu Mono\', \'Courier New\', monospace;"><code class="language-' . htmlspecialchars($lang) . '" style="white-space: pre;">' . $code . '</code></pre>';
        }, $content);
        
        // Inline code (avoid matching code blocks)
        $content = preg_replace_callback('/(?<!`)`([^`]+)`(?!`)/', function($matches) {
            return '<code class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm font-mono">' . htmlspecialchars($matches[1]) . '</code>';
        }, $content);
        
        // Headers (process in reverse order to avoid conflicts)
        $content = preg_replace('/^#### (.*)$/m', '<h4 class="text-xl font-semibold mt-6 mb-3 text-gray-800">$1</h4>', $content);
        $content = preg_replace('/^### (.*)$/m', '<h3 class="text-2xl font-bold mt-8 mb-4 text-primary">$1</h3>', $content);
        $content = preg_replace('/^## (.*)$/m', '<h2 class="text-3xl font-bold mt-10 mb-6 text-primary border-b border-gray-200 pb-2">$1</h2>', $content);
        $content = preg_replace('/^# (.*)$/m', '<h1 class="text-4xl font-bold mt-12 mb-8 text-primary">$1</h1>', $content);
        
        // Bold
        $content = preg_replace('/\*\*([^\*]+)\*\*/', '<strong class="font-semibold">$1</strong>', $content);
        
        // Italic
        $content = preg_replace('/\*([^\*]+)\*/', '<em>$1</em>', $content);
        
        // Ordered lists
        $content = preg_replace_callback('/^(\d+)\. (.*)$/m', function($matches) {
            return '<li class="ml-4 mb-2">' . $matches[2] . '</li>';
        }, $content);
        $content = preg_replace('/(<li class="ml-4 mb-2">.*<\/li>\n?)+/s', '<ol class="list-decimal ml-6 my-4 space-y-2">$0</ol>', $content);
        
        // Unordered lists
        $content = preg_replace('/^\- (.*)$/m', '<li class="ml-4 mb-2">$1</li>', $content);
        $content = preg_replace('/(<li class="ml-4 mb-2">.*<\/li>\n?)+/s', '<ul class="list-disc ml-6 my-4 space-y-2">$0</ul>', $content);
        
        // Blockquotes
        $content = preg_replace('/^> (.*)$/m', '<blockquote class="border-l-4 border-secondary pl-4 my-4 italic text-gray-600">$1</blockquote>', $content);
        
        // Horizontal rules
        $content = preg_replace('/^---$/m', '<hr class="my-8 border-gray-200">', $content);
        
        // Paragraphs (split by double newlines, but preserve lists and code blocks)
        // First, protect code blocks from being processed
        $codeBlocks = [];
        $content = preg_replace_callback('/<pre[^>]*>.*?<\/pre>/s', function($matches) use (&$codeBlocks) {
            $placeholder = '___CODE_BLOCK_' . count($codeBlocks) . '___';
            $codeBlocks[$placeholder] = $matches[0];
            return $placeholder;
        }, $content);
        
        $lines = explode("\n", $content);
        $result = [];
        $inList = false;
        $inCode = false;
        $currentParagraph = [];
        
        foreach ($lines as $line) {
            $trimmed = trim($line);
            
            // Check if this line is a code block placeholder
            if (isset($codeBlocks[$trimmed])) {
                if (!empty($currentParagraph)) {
                    $result[] = '<p class="mb-4 leading-relaxed">' . implode(' ', $currentParagraph) . '</p>';
                    $currentParagraph = [];
                }
                $result[] = $codeBlocks[$trimmed];
                continue;
            }
            
            // Skip empty lines
            if (empty($trimmed)) {
                if (!empty($currentParagraph) && !$inList && !$inCode) {
                    $result[] = '<p class="mb-4 leading-relaxed">' . implode(' ', $currentParagraph) . '</p>';
                    $currentParagraph = [];
                }
                continue;
            }
            
            // Check if we're in a list or code block
            if (preg_match('/^<(ul|ol|li|pre|code|blockquote|h[1-6])/', $trimmed)) {
                if (!empty($currentParagraph)) {
                    $result[] = '<p class="mb-4 leading-relaxed">' . implode(' ', $currentParagraph) . '</p>';
                    $currentParagraph = [];
                }
                $result[] = $line;
                continue;
            }
            
            // Regular text line
            if (!preg_match('/^<[\/]?(ul|ol|li|pre|code|blockquote|h[1-6])/', $trimmed)) {
                $currentParagraph[] = $trimmed;
            } else {
                $result[] = $line;
            }
        }
        
        if (!empty($currentParagraph)) {
            $result[] = '<p class="mb-4 leading-relaxed">' . implode(' ', $currentParagraph) . '</p>';
        }
        
        $content = implode("\n", $result);
        
        // Clean up empty paragraphs
        $content = preg_replace('/<p class="mb-4 leading-relaxed"><\/p>/', '', $content);
        $content = preg_replace('/<p class="mb-4 leading-relaxed">\s*<\/p>/', '', $content);
        
        return $content;
    }
    
    private function formatTitle($filename)
    {
        return ucwords(str_replace(['-', '_'], ' ', $filename));
    }
    
    private function getDocTitle($path)
    {
        return $this->formatTitle(basename($path));
    }
}

