#!/bin/bash
# 🚀 MesChain-Sync Enterprise - Deployment Script
# Production-ready deployment automation with rollback support

set -euo pipefail

# ====================================
# 🎨 COLORS & STYLING
# ====================================
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# ====================================
# 📋 CONFIGURATION
# ====================================
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
DATE=$(date +"%Y%m%d-%H%M%S")

# Default values
ENVIRONMENT="${ENVIRONMENT:-staging}"
VERSION="${VERSION:-latest}"
NAMESPACE="${NAMESPACE:-default}"
TIMEOUT="${TIMEOUT:-600}"
DRY_RUN="${DRY_RUN:-false}"
FORCE="${FORCE:-false}"
ROLLBACK="${ROLLBACK:-false}"

# ====================================
# 🛠️ UTILITY FUNCTIONS
# ====================================
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
    exit 1
}

info() {
    echo -e "${CYAN}ℹ️  $1${NC}"
}

# ====================================
# 📋 HELP FUNCTION
# ====================================
show_help() {
    cat << EOF
🚀 MesChain-Sync Enterprise Deployment Script

USAGE:
    $0 [OPTIONS]

OPTIONS:
    -e, --environment ENVIRONMENT    Target environment (staging/production) [default: staging]
    -v, --version VERSION           Version to deploy [default: latest]
    -n, --namespace NAMESPACE       Kubernetes namespace [default: default]
    -t, --timeout TIMEOUT          Deployment timeout in seconds [default: 600]
    -d, --dry-run                   Show what would be deployed without applying
    -f, --force                     Force deployment even if health checks fail
    -r, --rollback                  Rollback to previous version
    -h, --help                      Show this help message

EXAMPLES:
    # Deploy latest version to staging
    $0 --environment staging

    # Deploy specific version to production
    $0 --environment production --version v1.2.3

    # Dry run to see what would be deployed
    $0 --dry-run --environment production

    # Rollback production deployment
    $0 --rollback --environment production

ENVIRONMENT VARIABLES:
    KUBECONFIG                      Path to kubeconfig file
    DOCKER_REGISTRY                 Docker registry URL
    SLACK_WEBHOOK_URL               Slack webhook for notifications
    GITHUB_TOKEN                    GitHub token for releases

EOF
}

# ====================================
# 🔧 ARGUMENT PARSING
# ====================================
parse_arguments() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            -e|--environment)
                ENVIRONMENT="$2"
                shift 2
                ;;
            -v|--version)
                VERSION="$2"
                shift 2
                ;;
            -n|--namespace)
                NAMESPACE="$2"
                shift 2
                ;;
            -t|--timeout)
                TIMEOUT="$2"
                shift 2
                ;;
            -d|--dry-run)
                DRY_RUN="true"
                shift
                ;;
            -f|--force)
                FORCE="true"
                shift
                ;;
            -r|--rollback)
                ROLLBACK="true"
                shift
                ;;
            -h|--help)
                show_help
                exit 0
                ;;
            *)
                error "Unknown option: $1"
                ;;
        esac
    done
}

# ====================================
# ✅ VALIDATION FUNCTIONS
# ====================================
validate_environment() {
    if [[ ! "$ENVIRONMENT" =~ ^(staging|production)$ ]]; then
        error "Invalid environment: $ENVIRONMENT. Must be 'staging' or 'production'"
    fi
}

validate_dependencies() {
    local dependencies=("kubectl" "docker" "git" "curl")
    
    for dep in "${dependencies[@]}"; do
        if ! command -v "$dep" &> /dev/null; then
            error "Required dependency '$dep' is not installed"
        fi
    done
    
    success "All dependencies are available"
}

validate_cluster_access() {
    if ! kubectl cluster-info &> /dev/null; then
        error "Cannot connect to Kubernetes cluster. Check your kubeconfig"
    fi
    
    success "Kubernetes cluster access validated"
}

# ====================================
# 🔄 PRE-DEPLOYMENT CHECKS
# ====================================
check_cluster_resources() {
    log "Checking cluster resources..."
    
    local nodes_ready=$(kubectl get nodes --no-headers | awk '{print $2}' | grep -c "Ready" || echo "0")
    local total_nodes=$(kubectl get nodes --no-headers | wc -l)
    
    if [[ $nodes_ready -lt $total_nodes ]]; then
        warning "Not all nodes are ready ($nodes_ready/$total_nodes)"
    else
        success "All cluster nodes are ready ($nodes_ready/$total_nodes)"
    fi
}

check_image_availability() {
    log "Checking Docker image availability..."
    
    local image="ghcr.io/mestechsync/meschain-sync-enterprise:$VERSION"
    
    if docker manifest inspect "$image" &> /dev/null; then
        success "Docker image $image is available"
    else
        error "Docker image $image is not available"
    fi
}

# ====================================
# 🚀 DEPLOYMENT FUNCTIONS
# ====================================
backup_current_deployment() {
    log "Creating backup of current deployment..."
    
    local backup_dir="$PROJECT_ROOT/backups/deployments/$DATE"
    mkdir -p "$backup_dir"
    
    kubectl get deployment meschain-sync-enterprise -n "$NAMESPACE" -o yaml > "$backup_dir/deployment.yaml" 2>/dev/null || true
    kubectl get service meschain-sync-enterprise-service -n "$NAMESPACE" -o yaml > "$backup_dir/service.yaml" 2>/dev/null || true
    kubectl get configmap meschain-config -n "$NAMESPACE" -o yaml > "$backup_dir/configmap.yaml" 2>/dev/null || true
    
    success "Backup created at $backup_dir"
}

update_image_version() {
    log "Updating deployment image to version: $VERSION"
    
    local image="ghcr.io/mestechsync/meschain-sync-enterprise:$VERSION"
    
    if [[ "$DRY_RUN" == "true" ]]; then
        info "DRY RUN: Would update image to $image"
        return
    fi
    
    kubectl set image deployment/meschain-sync-enterprise \
        meschain-frontend="$image" \
        -n "$NAMESPACE"
    
    success "Image updated to $image"
}

wait_for_rollout() {
    log "Waiting for deployment rollout to complete..."
    
    if [[ "$DRY_RUN" == "true" ]]; then
        info "DRY RUN: Would wait for rollout completion"
        return
    fi
    
    if kubectl rollout status deployment/meschain-sync-enterprise \
        -n "$NAMESPACE" \
        --timeout="${TIMEOUT}s"; then
        success "Deployment rollout completed successfully"
    else
        error "Deployment rollout failed or timed out"
    fi
}

verify_deployment() {
    log "Verifying deployment health..."
    
    if [[ "$DRY_RUN" == "true" ]]; then
        info "DRY RUN: Would verify deployment health"
        return
    fi
    
    # Check pod status
    local ready_pods=$(kubectl get pods -l app=meschain-sync-enterprise -n "$NAMESPACE" --field-selector=status.phase=Running -o name | wc -l)
    local total_pods=$(kubectl get pods -l app=meschain-sync-enterprise -n "$NAMESPACE" -o name | wc -l)
    
    if [[ $ready_pods -eq $total_pods && $ready_pods -gt 0 ]]; then
        success "All pods are running ($ready_pods/$total_pods)"
    else
        warning "Not all pods are running ($ready_pods/$total_pods)"
        if [[ "$FORCE" != "true" ]]; then
            error "Deployment verification failed. Use --force to override"
        fi
    fi
    
    # Health check
    local service_ip=$(kubectl get service meschain-sync-enterprise-service -n "$NAMESPACE" -o jsonpath='{.spec.clusterIP}')
    if kubectl run test-pod --image=curlimages/curl --rm -i --restart=Never -- \
        curl -f "http://$service_ip/health" &> /dev/null; then
        success "Health check passed"
    else
        warning "Health check failed"
        if [[ "$FORCE" != "true" ]]; then
            error "Health verification failed. Use --force to override"
        fi
    fi
}

# ====================================
# 🔄 ROLLBACK FUNCTIONS
# ====================================
perform_rollback() {
    log "Performing rollback to previous version..."
    
    if [[ "$DRY_RUN" == "true" ]]; then
        info "DRY RUN: Would rollback to previous version"
        return
    fi
    
    kubectl rollout undo deployment/meschain-sync-enterprise -n "$NAMESPACE"
    
    log "Waiting for rollback to complete..."
    kubectl rollout status deployment/meschain-sync-enterprise -n "$NAMESPACE" --timeout="${TIMEOUT}s"
    
    success "Rollback completed successfully"
}

# ====================================
# 📢 NOTIFICATION FUNCTIONS
# ====================================
send_slack_notification() {
    local status="$1"
    local message="$2"
    
    if [[ -z "${SLACK_WEBHOOK_URL:-}" ]]; then
        return
    fi
    
    local color="good"
    local emoji="✅"
    
    if [[ "$status" == "failure" ]]; then
        color="danger"
        emoji="❌"
    elif [[ "$status" == "warning" ]]; then
        color="warning"
        emoji="⚠️"
    fi
    
    local payload=$(cat <<EOF
{
    "attachments": [
        {
            "color": "$color",
            "title": "$emoji MesChain-Sync Enterprise Deployment",
            "fields": [
                {
                    "title": "Environment",
                    "value": "$ENVIRONMENT",
                    "short": true
                },
                {
                    "title": "Version",
                    "value": "$VERSION",
                    "short": true
                },
                {
                    "title": "Status",
                    "value": "$message",
                    "short": false
                }
            ],
            "footer": "MesChain DevOps",
            "ts": $(date +%s)
        }
    ]
}
EOF
    )
    
    curl -X POST -H 'Content-type: application/json' \
        --data "$payload" \
        "$SLACK_WEBHOOK_URL" &> /dev/null || true
}

# ====================================
# 🎯 MAIN DEPLOYMENT FUNCTION
# ====================================
main() {
    log "🚀 Starting MesChain-Sync Enterprise Deployment"
    info "Environment: $ENVIRONMENT"
    info "Version: $VERSION"
    info "Namespace: $NAMESPACE"
    info "Dry Run: $DRY_RUN"
    
    # Validation
    validate_environment
    validate_dependencies
    validate_cluster_access
    
    # Pre-deployment checks
    check_cluster_resources
    check_image_availability
    
    if [[ "$ROLLBACK" == "true" ]]; then
        perform_rollback
        send_slack_notification "success" "Rollback completed successfully for $ENVIRONMENT"
    else
        # Backup current state
        backup_current_deployment
        
        # Deploy new version
        update_image_version
        wait_for_rollout
        verify_deployment
        
        send_slack_notification "success" "Deployment completed successfully for $ENVIRONMENT"
    fi
    
    success "🎉 Deployment process completed successfully!"
    
    # Show deployment info
    echo
    log "📊 Deployment Summary:"
    kubectl get deployment meschain-sync-enterprise -n "$NAMESPACE"
    echo
    kubectl get pods -l app=meschain-sync-enterprise -n "$NAMESPACE"
}

# ====================================
# 🔧 ERROR HANDLING
# ====================================
cleanup() {
    local exit_code=$?
    if [[ $exit_code -ne 0 ]]; then
        error "Deployment failed with exit code $exit_code"
        send_slack_notification "failure" "Deployment failed for $ENVIRONMENT"
    fi
}

trap cleanup EXIT

# ====================================
# 🚀 SCRIPT EXECUTION
# ====================================
parse_arguments "$@"
main 