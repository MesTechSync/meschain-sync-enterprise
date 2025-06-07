<?php
/**
 * MesChain-Sync Mobile App Manager Controller
 * 
 * ATOM-MZ009: Mobile-First Architecture Development
 * Developed by: MezBjen Team - Mobile Architecture Specialist
 * Date: June 13, 2025
 * 
 * @package    MesChain-Sync Enterprise
 * @subpackage Mobile App Management
 * @version    3.0.0
 * @author     MezBjen Development Team
 * @copyright  2025 MesTechSync Solutions
 * @license    Enterprise License
 */

class ControllerExtensionModuleMobileAppManager extends Controller {
    
    private $error = array();
    private $mobile_framework;
    private $logger;
    
    /**
     * Initialize Mobile App Manager Controller
     */
    public function __construct($registry) {
        parent::__construct($registry);
        
        // Load required models and libraries
        $this->load->model('extension/module/mobile_app_manager');
        $this->load->language('extension/module/mobile_app_manager');
        
        // Initialize Mobile Framework
        require_once(DIR_SYSTEM . 'library/meschain/mobile/mobile_architecture_framework_v3.php');
        $this->mobile_framework = new \MesChain\Mobile\MobileArchitectureFramework($this->db, $this->config);
        
        // Initialize logger
        require_once(DIR_SYSTEM . 'library/meschain/logger/mobile_logger.php');
        $this->logger = new \MesChain\Logger\MobileLogger('mobile_app_manager');
    }
    
    /**
     * Main Mobile App Manager Dashboard
     */
    public function index() {
        try {
            $this->document->setTitle($this->language->get('heading_title'));
            
            // Check permissions
            if (!$this->user->hasPermission('access', 'extension/module/mobile_app_manager')) {
                $this->session->data['error'] = $this->language->get('error_permission');
                $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
            }
            
            $data = $this->getCommonData();
            
            // Get mobile architecture status
            $data['architecture_status'] = $this->mobile_framework->getArchitectureStatus();
            
            // Get mobile app projects
            $data['mobile_projects'] = $this->getMobileProjects();
            
            // Get deployment status
            $data['deployment_status'] = $this->getDeploymentStatus();
            
            // Get performance metrics
            $data['performance_metrics'] = $this->getPerformanceMetrics();
            
            // Get API gateway status
            $data['api_gateway_status'] = $this->getAPIGatewayStatus();
            
            $this->logger->info('Mobile App Manager dashboard accessed successfully', [
                'user_id' => $this->user->getId(),
                'projects_count' => count($data['mobile_projects'])
            ]);
            
            $this->response->setOutput($this->load->view('extension/module/mobile_app_manager', $data));
            
        } catch (\Exception $e) {
            $this->logger->error('Mobile App Manager dashboard access failed: ' . $e->getMessage());
            $this->session->data['error'] = 'Mobile App Manager yüklenirken hata oluştu: ' . $e->getMessage();
            $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
    }
    
    /**
     * Generate React Native Project
     */
    public function generateReactNativeProject() {
        try {
            $this->checkAjaxPermission();
            
            $project_config = $this->request->post;
            
            // Generate React Native project structure
            $project_structure = $this->mobile_framework->generateReactNativeProject();
            
            // Create project files
            $project_id = $this->createMobileProject('react_native', $project_structure, $project_config);
            
            // Generate package.json
            $package_json = $this->generatePackageJson($project_config);
            
            // Generate app configuration
            $app_config = $this->generateAppConfig($project_config);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'project_id' => $project_id,
                'project_structure' => $project_structure,
                'package_json' => $package_json,
                'app_config' => $app_config,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('React Native project generation failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Generate iOS Native Project
     */
    public function generateiOSProject() {
        try {
            $this->checkAjaxPermission();
            
            $project_config = $this->request->post;
            
            // Generate iOS project structure
            $ios_structure = $this->mobile_framework->generateiOSNativeProject();
            
            // Create iOS project
            $project_id = $this->createMobileProject('ios_native', $ios_structure, $project_config);
            
            // Generate Xcode project configuration
            $xcode_config = $this->generateXcodeConfig($project_config);
            
            // Generate Info.plist
            $info_plist = $this->generateInfoPlist($project_config);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'project_id' => $project_id,
                'ios_structure' => $ios_structure,
                'xcode_config' => $xcode_config,
                'info_plist' => $info_plist,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('iOS project generation failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Generate Android Native Project
     */
    public function generateAndroidProject() {
        try {
            $this->checkAjaxPermission();
            
            $project_config = $this->request->post;
            
            // Generate Android project structure
            $android_structure = $this->mobile_framework->generateAndroidNativeProject();
            
            // Create Android project
            $project_id = $this->createMobileProject('android_native', $android_structure, $project_config);
            
            // Generate build.gradle
            $build_gradle = $this->generateBuildGradle($project_config);
            
            // Generate AndroidManifest.xml
            $android_manifest = $this->generateAndroidManifest($project_config);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'project_id' => $project_id,
                'android_structure' => $android_structure,
                'build_gradle' => $build_gradle,
                'android_manifest' => $android_manifest,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Android project generation failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Setup Progressive Web App
     */
    public function setupPWA() {
        try {
            $this->checkAjaxPermission();
            
            $pwa_config = $this->request->post;
            
            // Setup PWA
            $pwa_setup = $this->mobile_framework->setupProgressiveWebApp();
            
            // Create PWA project
            $project_id = $this->createMobileProject('pwa', $pwa_setup, $pwa_config);
            
            // Generate service worker
            $service_worker = $this->generateServiceWorker($pwa_config);
            
            // Generate web app manifest
            $web_manifest = $this->generateWebManifest($pwa_config);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'project_id' => $project_id,
                'pwa_setup' => $pwa_setup,
                'service_worker' => $service_worker,
                'web_manifest' => $web_manifest,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('PWA setup failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Configure API Gateway
     */
    public function configureAPIGateway() {
        try {
            $this->checkAjaxPermission();
            
            $gateway_config = $this->request->post;
            
            // Configure API Gateway
            $api_config = $this->mobile_framework->configureAPIGateway();
            
            // Save API Gateway configuration
            $this->model_extension_module_mobile_app_manager->saveAPIGatewayConfig($api_config, $gateway_config);
            
            // Generate API documentation
            $api_docs = $this->generateAPIDocumentation($api_config);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'api_config' => $api_config,
                'api_docs' => $api_docs,
                'endpoints_count' => count($api_config, COUNT_RECURSIVE),
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('API Gateway configuration failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Optimize Mobile Performance
     */
    public function optimizePerformance() {
        try {
            $this->checkAjaxPermission();
            
            $optimization_config = $this->request->post;
            
            // Run performance optimization
            $optimization_results = $this->mobile_framework->optimizeMobilePerformance();
            
            // Save optimization results
            $this->model_extension_module_mobile_app_manager->saveOptimizationResults($optimization_results);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'optimization_results' => $optimization_results,
                'performance_score' => $optimization_results['performance_score'],
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Mobile performance optimization failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Setup Cross-Platform Compatibility
     */
    public function setupCrossPlatform() {
        try {
            $this->checkAjaxPermission();
            
            $compatibility_config = $this->request->post;
            
            // Setup cross-platform compatibility
            $compatibility_setup = $this->mobile_framework->setupCrossPlatformCompatibility();
            
            // Save compatibility configuration
            $this->model_extension_module_mobile_app_manager->saveCrossPlatformConfig($compatibility_setup);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'compatibility_setup' => $compatibility_setup,
                'compatibility_score' => $compatibility_setup['compatibility_score'],
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Cross-platform setup failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Deploy Mobile App
     */
    public function deployApp() {
        try {
            $this->checkAjaxPermission();
            
            $deployment_config = $this->request->post;
            $project_id = $deployment_config['project_id'] ?? 0;
            
            if (!$project_id) {
                throw new \InvalidArgumentException('Project ID is required');
            }
            
            // Get project details
            $project = $this->model_extension_module_mobile_app_manager->getProject($project_id);
            
            if (!$project) {
                throw new \InvalidArgumentException('Project not found');
            }
            
            // Deploy based on platform
            $deployment_result = $this->deployMobileApp($project, $deployment_config);
            
            // Update deployment status
            $this->model_extension_module_mobile_app_manager->updateDeploymentStatus($project_id, $deployment_result);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'deployment_result' => $deployment_result,
                'project_id' => $project_id,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Mobile app deployment failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Get Mobile Projects
     */
    public function getProjects() {
        try {
            $this->checkAjaxPermission();
            
            $projects = $this->model_extension_module_mobile_app_manager->getProjects();
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'projects' => $projects,
                'total_projects' => count($projects),
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Get mobile projects failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Get Mobile Analytics
     */
    public function getAnalytics() {
        try {
            $this->checkAjaxPermission();
            
            $analytics = $this->model_extension_module_mobile_app_manager->getAnalytics();
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'analytics' => $analytics,
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Get mobile analytics failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Generate Mobile Documentation
     */
    public function generateDocumentation() {
        try {
            $this->checkAjaxPermission();
            
            $docs = $this->mobile_framework->generateMobileDocs();
            
            // Save documentation
            $this->model_extension_module_mobile_app_manager->saveDocumentation($docs);
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'documentation' => $docs,
                'total_docs' => count($docs, COUNT_RECURSIVE),
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
        } catch (\Exception $e) {
            $this->logger->error('Mobile documentation generation failed: ' . $e->getMessage());
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Create Mobile Project
     */
    private function createMobileProject($platform, $structure, $config) {
        $project_data = [
            'name' => $config['project_name'] ?? 'MesChain Mobile App',
            'platform' => $platform,
            'structure' => json_encode($structure),
            'config' => json_encode($config),
            'status' => 'created',
            'created_by' => $this->user->getId()
        ];
        
        return $this->model_extension_module_mobile_app_manager->createProject($project_data);
    }
    
    /**
     * Generate Package.json for React Native
     */
    private function generatePackageJson($config) {
        return [
            'name' => strtolower(str_replace(' ', '-', $config['project_name'] ?? 'meschain-mobile')),
            'version' => $config['version'] ?? '1.0.0',
            'private' => true,
            'scripts' => [
                'android' => 'react-native run-android',
                'ios' => 'react-native run-ios',
                'start' => 'react-native start',
                'test' => 'jest',
                'lint' => 'eslint .'
            ],
            'dependencies' => [
                'react' => '18.2.0',
                'react-native' => '0.74.0',
                '@react-navigation/native' => '^6.1.0',
                '@react-navigation/stack' => '^6.3.0',
                'react-native-vector-icons' => '^10.0.0',
                'react-native-async-storage' => '^1.19.0',
                'react-native-push-notification' => '^8.1.0',
                'react-native-biometrics' => '^3.0.0',
                'react-native-offline' => '^6.0.0'
            ],
            'devDependencies' => [
                '@babel/core' => '^7.20.0',
                '@babel/preset-env' => '^7.20.0',
                '@babel/runtime' => '^7.20.0',
                '@react-native/eslint-config' => '^0.74.0',
                '@react-native/metro-config' => '^0.74.0',
                '@react-native/typescript-config' => '^0.74.0',
                '@types/react' => '^18.2.6',
                '@types/react-test-renderer' => '^18.0.0',
                'babel-jest' => '^29.6.3',
                'eslint' => '^8.19.0',
                'jest' => '^29.6.3',
                'prettier' => '^2.8.8',
                'react-test-renderer' => '18.2.0',
                'typescript' => '5.0.4'
            ]
        ];
    }
    
    /**
     * Generate App Configuration
     */
    private function generateAppConfig($config) {
        return [
            'name' => $config['project_name'] ?? 'MesChain Mobile',
            'slug' => strtolower(str_replace(' ', '-', $config['project_name'] ?? 'meschain-mobile')),
            'version' => $config['version'] ?? '1.0.0',
            'orientation' => 'portrait',
            'icon' => './assets/icon.png',
            'userInterfaceStyle' => 'light',
            'splash' => [
                'image' => './assets/splash.png',
                'resizeMode' => 'contain',
                'backgroundColor' => '#ffffff'
            ],
            'assetBundlePatterns' => ['**/*'],
            'ios' => [
                'supportsTablet' => true,
                'bundleIdentifier' => $config['bundle_id'] ?? 'com.mestechsync.meschain'
            ],
            'android' => [
                'adaptiveIcon' => [
                    'foregroundImage' => './assets/adaptive-icon.png',
                    'backgroundColor' => '#FFFFFF'
                ],
                'package' => $config['package_name'] ?? 'com.mestechsync.meschain'
            ],
            'web' => [
                'favicon' => './assets/favicon.png'
            ]
        ];
    }
    
    /**
     * Get Mobile Projects
     */
    private function getMobileProjects() {
        return $this->model_extension_module_mobile_app_manager->getProjects();
    }
    
    /**
     * Get Deployment Status
     */
    private function getDeploymentStatus() {
        return $this->model_extension_module_mobile_app_manager->getDeploymentStatus();
    }
    
    /**
     * Get Performance Metrics
     */
    private function getPerformanceMetrics() {
        return $this->model_extension_module_mobile_app_manager->getPerformanceMetrics();
    }
    
    /**
     * Get API Gateway Status
     */
    private function getAPIGatewayStatus() {
        return $this->model_extension_module_mobile_app_manager->getAPIGatewayStatus();
    }
    
    /**
     * Deploy Mobile App
     */
    private function deployMobileApp($project, $config) {
        $platform = $project['platform'];
        
        switch ($platform) {
            case 'react_native':
                return $this->deployReactNativeApp($project, $config);
            case 'ios_native':
                return $this->deployiOSApp($project, $config);
            case 'android_native':
                return $this->deployAndroidApp($project, $config);
            case 'pwa':
                return $this->deployPWA($project, $config);
            default:
                throw new \InvalidArgumentException('Unsupported platform: ' . $platform);
        }
    }
    
    /**
     * Deploy React Native App
     */
    private function deployReactNativeApp($project, $config) {
        return [
            'platform' => 'react_native',
            'status' => 'deployed',
            'build_number' => time(),
            'deployment_url' => $config['deployment_url'] ?? null,
            'app_store_url' => $config['app_store_url'] ?? null,
            'play_store_url' => $config['play_store_url'] ?? null
        ];
    }
    
    /**
     * Deploy iOS App
     */
    private function deployiOSApp($project, $config) {
        return [
            'platform' => 'ios_native',
            'status' => 'deployed',
            'build_number' => time(),
            'app_store_url' => $config['app_store_url'] ?? null,
            'testflight_url' => $config['testflight_url'] ?? null
        ];
    }
    
    /**
     * Deploy Android App
     */
    private function deployAndroidApp($project, $config) {
        return [
            'platform' => 'android_native',
            'status' => 'deployed',
            'build_number' => time(),
            'play_store_url' => $config['play_store_url'] ?? null,
            'apk_url' => $config['apk_url'] ?? null
        ];
    }
    
    /**
     * Deploy PWA
     */
    private function deployPWA($project, $config) {
        return [
            'platform' => 'pwa',
            'status' => 'deployed',
            'build_number' => time(),
            'web_url' => $config['web_url'] ?? null,
            'manifest_url' => $config['manifest_url'] ?? null
        ];
    }
    
    /**
     * Generate API Documentation
     */
    private function generateAPIDocumentation($api_config) {
        $docs = [];
        
        foreach ($api_config as $category => $endpoints) {
            $docs[$category] = [
                'description' => ucfirst($category) . ' API endpoints',
                'endpoints' => []
            ];
            
            foreach ($endpoints as $name => $endpoint) {
                $docs[$category]['endpoints'][$name] = [
                    'url' => $endpoint,
                    'method' => $this->getEndpointMethod($name),
                    'description' => $this->getEndpointDescription($name),
                    'parameters' => $this->getEndpointParameters($name)
                ];
            }
        }
        
        return $docs;
    }
    
    /**
     * Helper Methods
     */
    private function getEndpointMethod($name) {
        $methods = [
            'login' => 'POST',
            'logout' => 'POST',
            'refresh' => 'POST',
            'list' => 'GET',
            'details' => 'GET',
            'update' => 'PUT',
            'create' => 'POST',
            'delete' => 'DELETE'
        ];
        
        return $methods[$name] ?? 'GET';
    }
    
    private function getEndpointDescription($name) {
        $descriptions = [
            'login' => 'User authentication',
            'logout' => 'User logout',
            'refresh' => 'Refresh authentication token',
            'list' => 'Get list of items',
            'details' => 'Get item details',
            'update' => 'Update item',
            'create' => 'Create new item',
            'delete' => 'Delete item'
        ];
        
        return $descriptions[$name] ?? 'API endpoint';
    }
    
    private function getEndpointParameters($name) {
        // Return sample parameters based on endpoint name
        return [];
    }
    
    /**
     * Check AJAX Permission
     */
    private function checkAjaxPermission() {
        if (!$this->user->hasPermission('access', 'extension/module/mobile_app_manager')) {
            throw new \Exception('Access denied');
        }
    }
    
    /**
     * Check Permission
     */
    private function checkPermission() {
        if (!$this->user->hasPermission('access', 'extension/module/mobile_app_manager')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }
    }
    
    /**
     * Get Common Template Data
     */
    private function getCommonData() {
        $data = array();
        
        // Language variables
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        // Error handling
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        // Success message
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/mobile_app_manager', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // URLs
        $data['action'] = $this->url->link('extension/module/mobile_app_manager', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
        
        // API URLs
        $data['api_generate_react_native'] = $this->url->link('extension/module/mobile_app_manager/generateReactNativeProject', 'user_token=' . $this->session->data['user_token'], true);
        $data['api_generate_ios'] = $this->url->link('extension/module/mobile_app_manager/generateiOSProject', 'user_token=' . $this->session->data['user_token'], true);
        $data['api_generate_android'] = $this->url->link('extension/module/mobile_app_manager/generateAndroidProject', 'user_token=' . $this->session->data['user_token'], true);
        $data['api_setup_pwa'] = $this->url->link('extension/module/mobile_app_manager/setupPWA', 'user_token=' . $this->session->data['user_token'], true);
        $data['api_configure_gateway'] = $this->url->link('extension/module/mobile_app_manager/configureAPIGateway', 'user_token=' . $this->session->data['user_token'], true);
        $data['api_optimize_performance'] = $this->url->link('extension/module/mobile_app_manager/optimizePerformance', 'user_token=' . $this->session->data['user_token'], true);
        $data['api_deploy_app'] = $this->url->link('extension/module/mobile_app_manager/deployApp', 'user_token=' . $this->session->data['user_token'], true);
        
        // Template data
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        return $data;
    }
}

?> 