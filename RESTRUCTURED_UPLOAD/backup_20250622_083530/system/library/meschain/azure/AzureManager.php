<?php
namespace MesChain\Azure;

class AzureManager {
    private $registry;
    private $config;
    private $services = [];
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->config = $registry->get('config');
        $this->loadServices();
    }
    
    private function loadServices() {
        // Azure servislerini yükle
        $this->services['blob'] = new BlobStorage($this->config);
        $this->services['queue'] = new QueueStorage($this->config);
        $this->services['insights'] = new ApplicationInsights($this->config);
        $this->services['keyvault'] = new KeyVault($this->config);
    }
    
    public function getService($name) {
        return isset($this->services[$name]) ? $this->services[$name] : null;
    }
    
    public function getStatus() {
        $status = [
            'connected' => false,
            'services' => []
        ];
        
        foreach ($this->services as $name => $service) {
            $status['services'][$name] = $service->getStatus();
            if ($service->isConnected()) {
                $status['connected'] = true;
            }
        }
        
        return $status;
    }
    
    public function getMetrics() {
        return $this->services['insights']->getMetrics();
    }
}
