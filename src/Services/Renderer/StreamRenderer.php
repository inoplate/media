<?php

namespace Inoplate\Media\Services\Renderer;

use Inoplate\Media\Services\Resizer\Resizer;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory;
use Session;
use Config;

class StreamRenderer implements Renderer
{
    /**
     * @var array
     */
    protected $headers;

    /**
     * @var Resizer
     */
    protected $resizer;

    /**
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $responseFactory;

    /*
     * @param Resizer         $resizer
     * @param Request         $request
     * @param ResponseFactory $responseFactory
     */
    public function __construct(Resizer $resizer, Request $request, ResponseFactory $responseFactory)
    {
        $this->resizer = $resizer;
        $this->request = $request;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Set headers
     * 
     * @param string|array $key
     * @param string       $value
     * @return self
     */
    public function setHeaders($key, $value = null)
    {
        if(is_array($key)) {
            foreach ($key as $field => $value) {
                $this->headers[$field] = $value;        
            }
        }else {
            $this->headers[$key] = $value;
        }

        return $this;
    }

    /**
     * Retrieve header
     * 
     * @param  key $key
     * @return array|string|null
     */
    public function getHeaders($key = null)
    {
        if(is_null($key)) {
            return $this->headers;
        }

        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }

    /**
     * Render file
     * 
     * @param  mixed $content
     * @return Reponse
     */
    public function render($content)
    {
        if(is_resource($content)) {
            $this->renderResource($content);
        }else {
            return $this->renderBinaryFile($content);
        }
    }

    /**
     * Rendering resource
     * 
     * @param  stream $content
     * @return Response
     */
    protected function renderResource($content)
    {
        $start = 0;
        $length = $this->headers['Content-Length'];
        $size = $length;
        $status = 200;

        if (false !== $range = $this->request->server('HTTP_RANGE', false)) {
            list($param, $range) = explode('=', $range);

            if (strtolower(trim($param)) !== 'bytes') {
                abort(400);
            }

            list($from, $to) = explode('-', $range);

            if ($from === '') {
                $end = $size - 1;
                $start = $end - intval($from);
            } elseif ($to === '') {
                $start = intval($from);
                $end = $size - 1;
            } else {
                $start = intval($from);
                $end = intval($to);
            }

            $length = $end - $start + 1;
            $status = 206;
            $this->setHeaders('Content-Range', sprintf('bytes %d-%d/%d', $start, $end, $size));
        }

        $response = $this->responseFactory->stream(function() use ($content, $start, $length) {
            fseek($content, $start, SEEK_SET); 
            echo fread($content, $length);

            flush();
            exit;
        }, $status, $this->getHeaders());

        // If there's a session we should save it now
        if (Config::get('session.driver') !== '')
        {
            Session::save();
        }

        if (ob_get_contents()) ob_end_clean();
        $response->send();
    }

    /**
     * Render binary file
     * 
     * @param  string $binaryFile
     * @return Response
     */
    protected function renderBinaryFile($binaryFile)
    {
        $status = 200;
        
        return $this->responseFactory->make($binaryFile, $status, $this->getHeaders());
    }
}