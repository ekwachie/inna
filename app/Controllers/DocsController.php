<?php

namespace app\Controllers;

use app\Core\Controller;
use app\Core\Request;
use app\Core\Response;
use app\Core\Utils\Documentation;

class DocsController extends Controller
{
    public function index(Request $request, Response $response)
    {
        return $this->document('getting-started/quickstart', $response);
    }

    public function show(Request $request, Response $response)
    {
        return $this->document($request->getRouteParam('path', 'README'), $response);
    }

    private function document(string $path, Response $response)
    {
        $documentation = new Documentation();
        $groups = $documentation->catalogue();
        $page = $documentation->page($path);
        $pages = array_merge(...array_column($groups, 'pages'));
        $position = array_search($path, array_column($pages, 'path'), true);
        if ($page === null) {
            $response->setStatusCode(404);
            $page = ['title' => 'Guide not found', 'content' => '<h1>Guide not found</h1><p>This documentation page does not exist. Choose a guide from the navigation or <a href="/documentation">start with the quickstart</a>.</p>', 'toc' => []];
        }
        $search = array_column($pages, 'search', 'path');
        return $this->render('docs/index', array_merge($page, [
            'groups' => $groups,
            'currentDoc' => $path,
            'breadcrumb' => $position === false ? 'Documentation' : ucwords(str_replace('-', ' ', dirname($path))),
            'previous' => $position !== false && $position > 0 ? $pages[$position - 1] : null,
            'next' => $position !== false ? ($pages[$position + 1] ?? null) : null,
            'searchJson' => json_encode($search, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
        ]));
    }
}

