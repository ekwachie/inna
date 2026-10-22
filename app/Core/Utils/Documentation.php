<?php

namespace app\Core\Utils;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\StringContainerInterface;

final class Documentation
{
    private string $directory;

    public function __construct(?string $directory = null)
    {
        $this->directory = $directory ?? dirname(__DIR__, 3) . '/docs';
    }

    public function catalogue(): array
    {
        $groups = [];
        $order = ['getting-started', 'the-basics', 'database', 'security', 'api', 'storage', 'services', 'utilities', 'advanced'];
        foreach ($order as $group) {
            $files = glob($this->directory . '/' . $group . '/*.md') ?: [];
            usort($files, static function ($a, $b) {
                $priority = ['quickstart' => 0, 'installation' => 1];
                return ($priority[basename($a, '.md')] ?? 2) <=> ($priority[basename($b, '.md')] ?? 2)
                    ?: strcmp($a, $b);
            });
            $pages = [];
            foreach ($files as $file) {
                $path = $group . '/' . basename($file, '.md');
                $pages[] = ['path' => $path, 'title' => $this->title(basename($file, '.md')),
                    'search' => $this->title($group) . ' ' . file_get_contents($file)];
            }
            if ($pages) {
                $groups[] = ['title' => $this->title($group), 'pages' => $pages];
            }
        }
        return $groups;
    }

    public function page(string $path): ?array
    {
        // Resolve within the documentation tree, including symlink targets.
        if (!preg_match('~^(?:[a-z0-9-]+/[a-z0-9-]+|README)$~D', $path)) {
            return null;
        }
        $root = realpath($this->directory);
        $file = realpath($this->directory . '/' . $path . '.md');
        if (!$root || !$file || !str_starts_with($file, $root . DIRECTORY_SEPARATOR) || !is_file($file)) {
            return null;
        }
        $toc = [];
        $used = [];
        $title = $this->title(basename($path));
        $environment = new Environment(['html_input' => 'strip', 'allow_unsafe_links' => false]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addEventListener(DocumentParsedEvent::class, function ($event) use (&$toc, &$used, &$title, $path) {
            $walker = $event->getDocument()->walker();
            while ($walk = $walker->next()) {
                if (!$walk->isEntering()) {
                    continue;
                }
                $node = $walk->getNode();
                if ($node instanceof Heading) {
                    $text = '';
                    $children = $node->walker();
                    while ($child = $children->next()) {
                        if ($child->isEntering() && $child->getNode() instanceof StringContainerInterface) {
                            $text .= $child->getNode()->getLiteral();
                        }
                    }
                    $slug = trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $text)), '-') ?: 'section';
                    $used[$slug] = ($used[$slug] ?? 0) + 1;
                    $id = $slug . ($used[$slug] > 1 ? '-' . $used[$slug] : '');
                    $node->data->set('attributes/id', $id);
                    if ($node->getLevel() === 1) {
                        $title = $text;
                    } elseif ($node->getLevel() === 2) {
                        $toc[] = ['id' => $id, 'title' => $text];
                    }
                }
                if ($node instanceof Link) {
                    $url = $node->getUrl();
                    if (preg_match('~^(?:https?://|mailto:|#)~i', $url)) {
                        continue;
                    }
                    $parts = explode('#', $url, 2);
                    $target = preg_replace('/\.md$/', '', ltrim($parts[0], '/'));
                    if (!is_file($this->directory . '/' . $target . '.md')) {
                        $target = dirname($path) . '/' . $target;
                    }
                    if (preg_match('~^(?:[a-z0-9-]+/[a-z0-9-]+|README)$~D', $target)
                        && is_file($this->directory . '/' . $target . '.md')) {
                        $node->setUrl('/documentation/' . $target . (isset($parts[1]) ? '#' . $parts[1] : ''));
                    }
                }
            }
        });
        $content = (string) (new MarkdownConverter($environment))->convert(file_get_contents($file));
        return ['content' => $content, 'toc' => $toc, 'title' => $title];
    }

    private function title(string $name): string
    {
        return str_replace(['Api', 'Jwt', 'Sms', 'Readme'], ['API', 'JWT', 'SMS', 'Overview'], ucwords(str_replace('-', ' ', $name)));
    }
}
