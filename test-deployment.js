// Simple test to run deployment
const { spawn } = require('child_process');

console.log('🚀 Testing MezBjen Dropshipping Optimization Deployment...\n');

const deployment = spawn('npx', ['ts-node', 'src/deployment/DropshippingOptimizationDeployer.ts'], {
  stdio: 'inherit',
  shell: true
});

deployment.on('close', (code) => {
  console.log(`\n📊 Deployment process finished with code: ${code}`);
  if (code === 0) {
    console.log('✅ Deployment successful!');
  } else {
    console.log('❌ Deployment failed or target not achieved');
  }
});

deployment.on('error', (error) => {
  console.error('💥 Deployment error:', error);
});
