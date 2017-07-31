<?php
/**
 * Created by PhpStorm.
 * User: shahrukhkhan
 * Date: 31/07/17
 * Time: 4:38 PM
 */

namespace Shahrukh;


use GuzzleHttp\Client;
use Shahrukh\Parsehub\ProjectTokenNotFoundException;

class Parsehub
{

    const GET = 'get';
    const POST = 'post';

    /**
     * Parsehub constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('PARSEHUB_KEY');
        $this->baseURI = 'https://www.parsehub.com/api/v2/';
    }

    /**
     * This will return the project object for a specific project.
     *
     * @param $projectToken
     * @return string
     * @throws ProjectTokenNotFoundException
     */
    public function getProject($projectToken)
    {
        if(!$projectToken)
        {
            throw new ProjectTokenNotFoundException('Please provide a project token');
        }

        $url = $this->buildProjectUri($projectToken);

        return $this->httpRequest($url, self::GET);
    }

    /**
     * This gets a list of projects in your account.
     * You may pass the following optional values as an array:
     *
     * offset = Specifies the offset from which to start the projects.
     *          E.g. in order to get projects 21-40, specify an offset
     *          of 20. Defaults to 0.
     *
     * limit = Specifies how many entries will be returned in projects.
     *         Accepts values between 1 and 20 inclusively. Defaults to 20.
     *
     * include_options = Adds options_json, main_template, main_site and webhook
     *                   to the entries of projects. Set this parameter to 1 if you
     *                   intend to use them in ParseHub API calls. This parameter requires
     *                   use of the offset and limit parameters to access the full list of projects.
     * @return string
     */
    public function getProjects()
    {
        $url = $this->buildProjectUri();

        return $this->httpRequest($url, self::GET);
    }

    /**
     * This will start running an instance of the project on the ParseHub cloud.
     * It will create a new run object.
     * This method will return immediately, while the run continues in the background.
     * You can use webhooks or polling to figure out when the data for this run is ready
     * in order to retrieve it. You may pass following values as an array:
     *
     * start_url
     * start_template
     * start_value_override
     *
     * @param $projectToken
     * @param array $params
     * @return string
     */
    public function runProject($projectToken, $params = [])
    {
        $url = $this->buildProjectUri($projectToken, $params);
        return $this->httpRequest($url, self::POST, $params);
    }

    /**
     * @param $runToken
     * @return string
     */
    public function getRun($runToken)
    {
        $url = $this->buildRunUri($runToken);
        return $this->httpRequest($url, self::GET);
    }

    /**
     * @param $runToken
     * @return string
     */
    public function getRunData($runToken)
    {
        $url = $this->buildRunUri($runToken, $options = [], 'data');
        return $this->httpRequest($url, self::GET);
    }

    public function getLastReadyRunData($projectToken)
    {
        $url = $this->buildProjectUri($projectToken, $options = [], $extras = ['last_ready_run', 'data']);
        return $this->httpRequest($url, self::GET);
    }

    public function cancelRun($runToken)
    {
        $url = $this->buildRunUri($runToken, $options = [], $extras = ['cancel']);
        return $this->httpRequest($url, self::POST);
    }

    /**
     * @param string $projectToken
     * @param array $options
     * @return string
     */
    protected function buildProjectUri($projectToken ='', $options = [], $extras = [])
    {
        $url = $this->baseURI.'projects';
        if($projectToken)
        {
            $url .= DIRECTORY_SEPARATOR. $projectToken;
        }
        if($extras)
        {
            $url .= implode('/', $extras);
        }
        if($options)
        {
            $url .= http_build_query($options);
        }
        $url .= "?api_key=$this->apiKey";
        return $url;
    }

    /**
     * @param string $runToken
     * @param array $options
     * @return string
     */
    protected function buildRunUri($runToken ='', $options = [], $extras = [])
    {
        $url = $this->baseURI.'runs';
        if($runToken)
        {
            $url .= DIRECTORY_SEPARATOR. $runToken;
        }
        if($extras)
        {
            $url .= implode('/', $extras);
        }
        if($options)
        {
            $url .= http_build_query($options);
        }
        $url .= "?api_key=$this->apiKey";
        return $url;
    }

    /**
     * @param $url
     * @param $method
     * @return string
     */
    public function httpRequest($url, $method, $params = [])
    {
        if($method === self::GET) {
            $request = $this->client->request($method, $url);
        }
        else
        {
            $request = $this->client->request($method, $url, $params);
        }

        $response             = new \stdClass();
        $response->statusCode = $request->getStatusCode();
        $response->headers    = $request->getHeader('content-type');
        $response->body       = json_decode($request->getBody());

        return json_encode($response);
    }
}