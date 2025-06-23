<?php
echo "=== CRON Jobs Menu Fix ===\n\n";

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=opencart4', 'root', '1234');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "1. Checking CRON permissions...\n";

// Check current permissions
$stmt = $pdo->query("SELECT permission FROM oc_user_group WHERE user_group_id = 1");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$permissions = json_decode($result['permission'], true);

$has_cron_access = in_array('marketplace/cron', $permissions['access'] ?? []);
$has_cron_modify = in_array('marketplace/cron', $permissions['modify'] ?? []);

echo "CRON access permission: " . ($has_cron_access ? "✅ YES" : "❌ NO") . "\n";
echo "CRON modify permission: " . ($has_cron_modify ? "✅ YES" : "❌ NO") . "\n";

if (!$has_cron_access || !$has_cron_modify) {
    echo "\n2. Adding CRON permissions...\n";
    
    // Add CRON permissions
    if (!$has_cron_access) {
        $permissions['access'][] = 'marketplace/cron';
        echo "✅ Added CRON access permission\n";
    }
    
    if (!$has_cron_modify) {
        $permissions['modify'][] = 'marketplace/cron';
        echo "✅ Added CRON modify permission\n";
    }
    
    // Update permissions
    $stmt = $pdo->prepare("UPDATE oc_user_group SET permission = ? WHERE user_group_id = 1");
    $stmt->execute([json_encode($permissions)]);
    echo "✅ Permissions updated in database\n";
} else {
    echo "\n✅ CRON permissions already exist\n";
}

echo "\n3. Checking CRON controller exists...\n";

$cron_controller = 'opencart4/admin/controller/marketplace/cron.php';
if (file_exists($cron_controller)) {
    echo "✅ CRON controller exists: $cron_controller\n";
} else {
    echo "❌ MISSING: $cron_controller\n";
    echo "   Creating CRON controller...\n";
    
    // Create directory if not exists
    if (!is_dir('opencart4/admin/controller/marketplace')) {
        mkdir('opencart4/admin/controller/marketplace', 0755, true);
    }
    
    // Create basic CRON controller
    $cron_content = '<?php
namespace Opencart\Admin\Controller\Marketplace;
class Cron extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language("marketplace/cron");
        
        $this->document->setTitle($this->language->get("heading_title"));
        
        $data["breadcrumbs"] = [];
        
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/dashboard", "user_token=" . $this->session->data["user_token"])
        ];
        
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("heading_title"),
            "href" => $this->url->link("marketplace/cron", "user_token=" . $this->session->data["user_token"])
        ];
        
        $data["user_token"] = $this->session->data["user_token"];
        
        $data["header"] = $this->load->controller("common/header");
        $data["column_left"] = $this->load->controller("common/column_left");
        $data["footer"] = $this->load->controller("common/footer");
        
        $this->response->setOutput($this->load->view("marketplace/cron", $data));
    }
}
?>';
    
    file_put_contents($cron_controller, $cron_content);
    echo "✅ CRON controller created\n";
}

echo "\n4. Checking language files...\n";

$lang_files = [
    'opencart4/admin/language/en-gb/marketplace/cron.php'
];

foreach ($lang_files as $lang_file) {
    if (file_exists($lang_file)) {
        echo "✅ Language file exists: $lang_file\n";
    } else {
        echo "❌ MISSING: $lang_file\n";
        echo "   Creating language file...\n";
        
        // Create directory if not exists
        $dir = dirname($lang_file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $lang_content = '<?php
// Heading
$_["heading_title"] = "CRON Jobs";

// Text
$_["text_list"] = "CRON Job List";
$_["text_add"] = "Add CRON Job";
$_["text_edit"] = "Edit CRON Job";
$_["text_cron"] = "CRON Jobs";
$_["text_enabled"] = "Enabled";
$_["text_disabled"] = "Disabled";

// Column
$_["column_name"] = "CRON Job Name";
$_["column_command"] = "Command";
$_["column_status"] = "Status";
$_["column_date_added"] = "Date Added";
$_["column_action"] = "Action";

// Entry
$_["entry_name"] = "CRON Job Name";
$_["entry_command"] = "Command";
$_["entry_status"] = "Status";

// Error
$_["error_permission"] = "Warning: You do not have permission to modify CRON jobs!";
?>';
        
        file_put_contents($lang_file, $lang_content);
        echo "✅ Language file created\n";
    }
}

echo "\n5. Checking template file...\n";

$template_file = 'opencart4/admin/view/template/marketplace/cron.twig';
if (file_exists($template_file)) {
    echo "✅ Template file exists: $template_file\n";
} else {
    echo "❌ MISSING: $template_file\n";
    echo "   Creating template file...\n";
    
    // Create directory if not exists
    $dir = dirname($template_file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $template_content = '{{ header }}{{ column_left }}
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <a href="#" class="btn btn-primary"><i class="fa fa-plus"></i> {{ text_add }}</a>
      </div>
      <h1>{{ heading_title }}</h1>
      <ul class="breadcrumb">
        {% for breadcrumb in breadcrumbs %}
          <li><a href="{{ breadcrumb.href }}">{{ breadcrumb.text }}</a></li>
        {% endfor %}
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-list"></i> {{ text_list }}</h3>
      </div>
      <div class="panel-body">
        <div class="alert alert-info">
          <i class="fa fa-info-circle"></i> CRON Jobs functionality is available for managing scheduled tasks.
          <br><strong>Note:</strong> This requires server configuration to run CRON jobs properly.
        </div>
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead>
              <tr>
                <td class="text-left">{{ column_name }}</td>
                <td class="text-left">{{ column_command }}</td>
                <td class="text-left">{{ column_status }}</td>
                <td class="text-left">{{ column_date_added }}</td>
                <td class="text-right">{{ column_action }}</td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="5" class="text-center">No CRON jobs configured</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
{{ footer }}';
    
    file_put_contents($template_file, $template_content);
    echo "✅ Template file created\n";
}

echo "\n6. Clear cache...\n";
$cache_files = glob('opencart4/system/storage/cache/*');
foreach ($cache_files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}
echo "✅ Cache cleared\n";

echo "\n=== CRON JOBS MENU FIX COMPLETE ===\n";
echo "🎯 CRON Jobs should now appear in Extensions menu!\n";
echo "📍 Location: Extensions → CRON Jobs\n";
echo "🔄 Refresh your admin panel to see the changes\n";
?> 