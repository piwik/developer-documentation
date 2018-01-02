<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace helpers;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class CacheMiddleware.
 *
 * Only GET requests are cached so far and only by path. So if you request a URL like "/foo" and URL like "/foo?bar=1"
 * the same cache key is used.
 *
 * @package helpers
 */
class CacheMiddleware
{
    public function __invoke(Request $req, Response $res, callable $next) {

        if ($this->shouldCache($req)) {
            $content = Cache::get($this->getCacheKey($req));

            if (!empty($content)) {
                if ($this->isJsonData($req)) {
                    $res = $res->withHeader('Content-Type', 'application/json');
                    $res->getBody()->write($content);
                } else{
                    $res->getBody()->write($content . "\n<!-- Cached response -->");
                }
                return $res;
            }
        }
        /** @var Response $res */
        $res = $next($req, $res);
        $res->getBody()->rewind();
        $content = $res->getBody()->getContents();
        if ($this->shouldCache($req) && 200 == $res->getStatusCode() && !$this->hadIncludeFileError($content)) {
            Cache::set($this->getCacheKey($req), $content);
        }

        return $res;
    }

    /**
     * We currently ignore query parameters...
     *
     * @param $path
     * @return mixed|string
     */
    private function pathToCacheKey($path) {
        if ('/' == $path || empty($path)) {
            return 'index';
        }

        $path = strip_tags($path);
        $path = trim($path);
        $path = preg_replace('/\//', '-', $path);
        $path = preg_replace('/\s/', '-', $path);
        $path = preg_replace('/[^a-zA-Z0-9\-]/', '', $path);
        $path = strtolower($path);

        return $path;
    }

    private function shouldCache(Request $req) {
        return $req->isGet();
    }

    private function hadIncludeFileError($content) {
        return (strpos($content, "Error while retrieving") !== false);
    }

    private function isJsonData(Request $req) {
        return substr($req->getUri()->getPath(), 0, 6) === '/data/';
    }

    private function getCacheKey(Request $req) {
        $piwikVersion = Environment::getPiwikVersion();
        if ($this->isJsonData($req)) {
            $type = "json";
        } else {
            $type = "html";
        }

        return $piwikVersion . '_' . $this->pathToCacheKey($req->getUri()->getPath()) . "." . $type;
    }
}