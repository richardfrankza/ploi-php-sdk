<?php

namespace Ploi\Resources;

use stdClass;
use Ploi\Ploi;
use Ploi\Exceptions\Http\NotValid;
use Ploi\Exceptions\Resource\RequiresId;

class Server extends Resource
{
    private $endpoint = 'servers';

    public function __construct(Ploi $ploi = null, int $id = null)
    {
        parent::__construct($ploi, $id);

        $this->setEndpoint($this->endpoint);
    }

    public function get(int $id = null)
    {
        if ($id) {
            $this->setId($id);
        }

        if ($this->getId()) {
            $this->setEndpoint($this->endpoint . '/' . $this->getId());
        }

        return $this->getPloi()->makeAPICall($this->getEndpoint());
    }

    public function delete(int $id = null)
    {
        if ($id) {
            $this->setId($id);
        }

        if ($this->getId()) {
            $this->setEndpoint($this->endpoint . '/' . $this->getId());
        }

        return $this->getPloi()->makeAPICall($this->getEndpoint(), 'delete');
    }

    public function logs(int $id = null)
    {
        if ($id) {
            $this->setId($id);
        }

        if (!$this->getId()) {
            throw new RequiresId('No server ID set');
        }

        $this->setEndpoint($this->endpoint . '/' . $this->getId() . '/logs');

        return $this->getPloi()->makeAPICall($this->getEndpoint());
    }

    public function create(
        string $name,
        int $provider,
        int $region,
        int $plan
    ): stdClass {

        // Remove the id
        $this->setId(null);

        // Set the options
        $options = [
            'body' => json_encode([
                'name' => $name,
                'plan' => $plan,
                'region' => $region,
                'credential' => $provider,
                'type' => 'server',
                'database_type' => 'mysql',
                'webserver_type' => 'nginx',
                'php_version' => '7.4'
            ]),
        ];

        // Make the request
        try {
            $response = $this->getPloi()->makeAPICall($this->getEndpoint(), 'post', $options);
        } catch (NotValid $exception) {
            $errors = json_decode($exception->getMessage())->errors;

            dd($errors);

            throw $exception;
        }

        // Set the id of the site
        $this->setId($response->getJson()->data->id);

        // Return the data
        return $response->getData();
    }

    public function refreshOpcache(int $id = null): stdClass
    {
        if ($id) {
            $this->setId($id);
        }

        if (!$this->getId()) {
            throw new RequiresId('No server ID set');
        }

        $this->setEndpoint($this->endpoint . '/' . $this->getId());

        $response = $this->getPloi()->makeAPICall($this->getEndpoint() . '/refresh-opcache', 'post');

        return $response->getJson();
    }

    public function enableOpcache(int $id = null): stdClass
    {
        if ($id) {
            $this->setId($id);
        }

        if (!$this->getId()) {
            throw new RequiresId('No server ID set');
        }

        $this->setEndpoint($this->endpoint . '/' . $this->getId());

        $response = $this->getPloi()->makeAPICall($this->getEndpoint() . '/enable-opcache', 'post');

        return $response->getJson();
    }

    public function disableOpcache(int $id = null): stdClass
    {
        if ($id) {
            $this->setId($id);
        }

        if (!$this->getId()) {
            throw new RequiresId('No server ID set');
        }

        $this->setEndpoint($this->endpoint . '/' . $this->getId());

        $response = $this->getPloi()->makeAPICall($this->getEndpoint() . '/disable-opcache', 'delete');

        return $response->getJson();
    }

    public function phpVersions(int $id = null): stdClass
    {
        if ($id) {
            $this->setId($id);
        }

        if (!$this->getId()) {
            throw new RequiresId('No server ID set');
        }

        $this->setEndpoint($this->endpoint . '/' . $this->getId());

        $response = $this->getPloi()->makeAPICall($this->getEndpoint() . '/php/versions', 'get');

        return $response->getJson();
    }

    public function sites($id = null): Site
    {
        return new Site($this, $id);
    }

    public function services(string $serviceName = null): Service
    {
        return new Service($this, $serviceName);
    }

    public function databases($id = null): Database
    {
        return new Database($this, $id);
    }
    public function cronjobs($id = null): Cronjob
    {
        return new Cronjob($this, $id);
    }

    public function networkRules($id = null): NetworkRule
    {
        return new NetworkRule($this, $id);
    }

    public function systemUsers($id = null): SystemUser
    {
        return new SystemUser($this, $id);
    }

    public function daemons($id = null): Daemon
    {
        return new Daemon($this, $id);
    }
}
