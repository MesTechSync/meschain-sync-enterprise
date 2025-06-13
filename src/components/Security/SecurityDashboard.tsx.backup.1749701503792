/**
 * Security Dashboard Component
 * Real-time security monitoring, vulnerability management, and threat intelligence
 */

import React, { useState, useEffect, useCallback, useMemo } from 'react';
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  Badge,
  Button,
  Progress,
  Alert,
  AlertDescription,
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableBody,
  TableCell,
  Tabs,
  TabsList,
  TabsTrigger,
  TabsContent,
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  Input,
  Textarea,
  Switch,
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger
} from '@/components/ui';
import {
  ShieldCheck,
  ShieldAlert,
  ShieldX,
  AlertTriangle,
  Bug,
  Zap,
  Activity,
  TrendingUp,
  TrendingDown,
  Clock,
  Filter,
  Download,
  Refresh,
  Settings,
  Eye,
  EyeOff,
  Play,
  Pause,
  CheckCircle,
  XCircle,
  AlertCircle,
  Info,
  Lock,
  Unlock,
  Monitor,
  Database,
  Globe,
  Code,
  Network,
  Server
} from 'lucide-react';
import { 
  LineChart, 
  Line, 
  AreaChart, 
  Area, 
  XAxis, 
  YAxis, 
  CartesianGrid, 
  Tooltip as RechartsTooltip, 
  Legend, 
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell,
  BarChart,
  Bar
} from 'recharts';
import VulnerabilityScanner, { Vulnerability, ScanResult } from '../../services/security/VulnerabilityScanner';
import SecurityManager from '../../services/security/SecurityManager';

// Types
interface SecurityMetrics {
  totalVulnerabilities: number;
  openVulnerabilities: number;
  criticalVulnerabilities: number;
  averageRiskScore: number;
  complianceScore: number;
  lastScanTime?: Date;
  threatLevel: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  securityEvents: number;
  blockedAttacks: number;
  securityScore: number;
}

interface ThreatIntelligence {
  id: string;
  type: 'MALWARE' | 'PHISHING' | 'DDOS' | 'INTRUSION' | 'DATA_BREACH';
  severity: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  source: string;
  timestamp: Date;
  description: string;
  indicators: string[];
  mitigation: string;
  affected: number;
}

interface SecurityEvent {
  id: string;
  timestamp: Date;
  type: 'ATTACK' | 'VULNERABILITY' | 'BREACH' | 'AUDIT' | 'COMPLIANCE';
  severity: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  source: string;
  target: string;
  description: string;
  status: 'OPEN' | 'INVESTIGATING' | 'RESOLVED' | 'FALSE_POSITIVE';
  assignee?: string;
}

const SecurityDashboard: React.FC = () => {
  // State Management
  const [vulnerabilityScanner] = useState(() => new VulnerabilityScanner());
  const [securityManager] = useState(() => new SecurityManager());
  const [metrics, setMetrics] = useState<SecurityMetrics>({
    totalVulnerabilities: 0,
    openVulnerabilities: 0,
    criticalVulnerabilities: 0,
    averageRiskScore: 0,
    complianceScore: 100,
    threatLevel: 'LOW',
    securityEvents: 0,
    blockedAttacks: 0,
    securityScore: 95
  });
  
  const [vulnerabilities, setVulnerabilities] = useState<Vulnerability[]>([]);
  const [scanResults, setScanResults] = useState<ScanResult[]>([]);
  const [threats, setThreats] = useState<ThreatIntelligence[]>([]);
  const [securityEvents, setSecurityEvents] = useState<SecurityEvent[]>([]);
  const [isScanning, setIsScanning] = useState(false);
  const [selectedVulnerability, setSelectedVulnerability] = useState<Vulnerability | null>(null);
  const [filters, setFilters] = useState({
    severity: 'ALL',
    status: 'ALL',
    type: 'ALL',
    timeRange: '7d'
  });
  const [realTimeMonitoring, setRealTimeMonitoring] = useState(true);

  // Colors for charts
  const SEVERITY_COLORS = {
    CRITICAL: '#dc2626',
    HIGH: '#ea580c',
    MEDIUM: '#d97706',
    LOW: '#65a30d',
    INFO: '#0891b2'
  };

  const CHART_COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

  // Data Loading
  useEffect(() => {
    loadSecurityData();
    
    const interval = setInterval(loadSecurityData, 30000); // Update every 30 seconds
    return () => clearInterval(interval);
  }, []);

  const loadSecurityData = useCallback(async () => {
    try {
      // Load metrics
      const newMetrics = vulnerabilityScanner.getSecurityMetrics();
      setMetrics(prev => ({
        ...prev,
        ...newMetrics,
        threatLevel: calculateThreatLevel(newMetrics),
        securityScore: calculateSecurityScore(newMetrics)
      }));

      // Load vulnerabilities
      const vulns = vulnerabilityScanner.getVulnerabilities();
      setVulnerabilities(vulns);

      // Load scan results
      const scans = vulnerabilityScanner.getScanResults(10);
      setScanResults(scans);

      // Mock threat intelligence data
      setThreats(generateMockThreats());
      
      // Mock security events
      setSecurityEvents(generateMockSecurityEvents());

    } catch (error) {
      console.error('Error loading security data:', error);
    }
  }, [vulnerabilityScanner]);

  // Utility Functions
  const calculateThreatLevel = (metrics: any): 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL' => {
    if (metrics.criticalVulnerabilities > 0) return 'CRITICAL';
    if (metrics.averageRiskScore > 70) return 'HIGH';
    if (metrics.averageRiskScore > 40) return 'MEDIUM';
    return 'LOW';
  };

  const calculateSecurityScore = (metrics: any): number => {
    let score = 100;
    score -= metrics.criticalVulnerabilities * 20;
    score -= metrics.openVulnerabilities * 2;
    score = Math.max(0, score);
    return score;
  };

  const generateMockThreats = (): ThreatIntelligence[] => [
    {
      id: 'threat-1',
      type: 'MALWARE',
      severity: 'HIGH',
      source: 'External Feed',
      timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000),
      description: 'New ransomware variant targeting e-commerce platforms',
      indicators: ['malware.exe', '192.168.1.100', 'evil-domain.com'],
      mitigation: 'Update antivirus signatures, block suspicious IPs',
      affected: 1247
    },
    {
      id: 'threat-2',
      type: 'PHISHING',
      severity: 'MEDIUM',
      source: 'Threat Intel',
      timestamp: new Date(Date.now() - 6 * 60 * 60 * 1000),
      description: 'Phishing campaign impersonating popular marketplace',
      indicators: ['fake-site.com', 'phishing@evil.com'],
      mitigation: 'Block domains, educate users',
      affected: 532
    }
  ];

  const generateMockSecurityEvents = (): SecurityEvent[] => [
    {
      id: 'event-1',
      timestamp: new Date(Date.now() - 30 * 60 * 1000),
      type: 'ATTACK',
      severity: 'HIGH',
      source: '203.0.113.15',
      target: '/api/admin',
      description: 'Multiple failed login attempts to admin panel',
      status: 'INVESTIGATING',
      assignee: 'security-team'
    },
    {
      id: 'event-2',
      timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000),
      type: 'VULNERABILITY',
      severity: 'CRITICAL',
      source: 'Auto Scanner',
      target: 'payment-service',
      description: 'SQL injection vulnerability detected',
      status: 'OPEN'
    }
  ];

  // Event Handlers
  const handleStartScan = async () => {
    setIsScanning(true);
    try {
      await vulnerabilityScanner.startScan();
      await loadSecurityData();
    } catch (error) {
      console.error('Scan failed:', error);
    } finally {
      setIsScanning(false);
    }
  };

  const handleUpdateVulnerabilityStatus = (id: string, status: Vulnerability['status']) => {
    vulnerabilityScanner.updateVulnerabilityStatus(id, status);
    loadSecurityData();
  };

  const handleExportReport = () => {
    const report = {
      timestamp: new Date().toISOString(),
      metrics,
      vulnerabilities: vulnerabilities.slice(0, 50),
      threats: threats.slice(0, 20),
      events: securityEvents.slice(0, 30)
    };
    
    const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `security-report-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  };

  // Chart Data
  const threatTrendData = useMemo(() => {
    const days = Array.from({ length: 7 }, (_, i) => {
      const date = new Date();
      date.setDate(date.getDate() - (6 - i));
      return {
        date: date.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit' }),
        threats: Math.floor(Math.random() * 50) + 10,
        blocked: Math.floor(Math.random() * 100) + 50,
        vulnerabilities: Math.floor(Math.random() * 20) + 5
      };
    });
    return days;
  }, []);

  const vulnerabilityDistribution = useMemo(() => {
    const distribution = vulnerabilities.reduce((acc, vuln) => {
      acc[vuln.severity] = (acc[vuln.severity] || 0) + 1;
      return acc;
    }, {} as Record<string, number>);

    return Object.entries(distribution).map(([severity, count]) => ({
      name: severity,
      value: count,
      color: SEVERITY_COLORS[severity as keyof typeof SEVERITY_COLORS]
    }));
  }, [vulnerabilities]);

  const complianceData = [
    { name: 'GDPR', score: 95, color: '#10b981' },
    { name: 'PCI-DSS', score: 88, color: '#3b82f6' },
    { name: 'ISO 27001', score: 92, color: '#f59e0b' },
    { name: 'SOX', score: 85, color: '#8b5cf6' }
  ];

  // Filtered data
  const filteredVulnerabilities = useMemo(() => {
    return vulnerabilities.filter(vuln => {
      if (filters.severity !== 'ALL' && vuln.severity !== filters.severity) return false;
      if (filters.status !== 'ALL' && vuln.status !== filters.status) return false;
      if (filters.type !== 'ALL' && vuln.type !== filters.type) return false;
      return true;
    });
  }, [vulnerabilities, filters]);

  return (
    <div className="p-6 space-y-6 bg-gray-50 min-h-screen">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Güvenlik Dashboard</h1>
          <p className="text-gray-600 mt-1">Gerçek zamanlı güvenlik izleme ve yönetim</p>
        </div>
        
        <div className="flex items-center gap-3">
          <div className="flex items-center gap-2">
            <span className="text-sm text-gray-600">Canlı İzleme</span>
            <Switch
              checked={realTimeMonitoring}
              onCheckedChange={setRealTimeMonitoring}
            />
          </div>
          
          <Button onClick={handleExportReport} variant="outline">
            <Download className="w-4 h-4 mr-2" />
            Rapor İndir
          </Button>
          
          <Button onClick={handleStartScan} disabled={isScanning}>
            {isScanning ? (
              <>
                <Pause className="w-4 h-4 mr-2" />
                Taranıyor...
              </>
            ) : (
              <>
                <Play className="w-4 h-4 mr-2" />
                Tarama Başlat
              </>
            )}
          </Button>
        </div>
      </div>

      {/* Security Score Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card className="border-l-4 border-l-blue-500">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Güvenlik Skoru</p>
                <p className="text-3xl font-bold text-blue-600">{metrics.securityScore}%</p>
              </div>
              <ShieldCheck className="w-8 h-8 text-blue-500" />
            </div>
            <Progress value={metrics.securityScore} className="mt-3" />
          </CardContent>
        </Card>

        <Card className="border-l-4 border-l-red-500">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Kritik Açıklar</p>
                <p className="text-3xl font-bold text-red-600">{metrics.criticalVulnerabilities}</p>
              </div>
              <ShieldX className="w-8 h-8 text-red-500" />
            </div>
            <p className="text-sm text-gray-500 mt-2">
              Toplam {metrics.totalVulnerabilities} açık
            </p>
          </CardContent>
        </Card>

        <Card className="border-l-4 border-l-yellow-500">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Tehdit Seviyesi</p>
                <Badge 
                  variant={metrics.threatLevel === 'CRITICAL' ? 'destructive' : 'secondary'}
                  className="text-sm font-bold"
                >
                  {metrics.threatLevel}
                </Badge>
              </div>
              <AlertTriangle className="w-8 h-8 text-yellow-500" />
            </div>
            <p className="text-sm text-gray-500 mt-2">
              {metrics.securityEvents} güvenlik olayı
            </p>
          </CardContent>
        </Card>

        <Card className="border-l-4 border-l-green-500">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Uyumluluk</p>
                <p className="text-3xl font-bold text-green-600">{metrics.complianceScore}%</p>
              </div>
              <CheckCircle className="w-8 h-8 text-green-500" />
            </div>
            <Progress value={metrics.complianceScore} className="mt-3" />
          </CardContent>
        </Card>
      </div>

      {/* Main Content Tabs */}
      <Tabs defaultValue="overview" className="space-y-6">
        <TabsList className="grid w-full grid-cols-6">
          <TabsTrigger value="overview">Genel Bakış</TabsTrigger>
          <TabsTrigger value="vulnerabilities">Açıklar</TabsTrigger>
          <TabsTrigger value="threats">Tehditler</TabsTrigger>
          <TabsTrigger value="events">Olaylar</TabsTrigger>
          <TabsTrigger value="compliance">Uyumluluk</TabsTrigger>
          <TabsTrigger value="reports">Raporlar</TabsTrigger>
        </TabsList>

        {/* Overview Tab */}
        <TabsContent value="overview" className="space-y-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Threat Trend Chart */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <TrendingUp className="w-5 h-5" />
                  Güvenlik Trendleri (7 Gün)
                </CardTitle>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <LineChart data={threatTrendData}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="date" />
                    <YAxis />
                    <RechartsTooltip />
                    <Legend />
                    <Line 
                      type="monotone" 
                      dataKey="threats" 
                      stroke="#ef4444" 
                      name="Tehditler"
                    />
                    <Line 
                      type="monotone" 
                      dataKey="blocked" 
                      stroke="#10b981" 
                      name="Engellenen"
                    />
                    <Line 
                      type="monotone" 
                      dataKey="vulnerabilities" 
                      stroke="#f59e0b" 
                      name="Yeni Açıklar"
                    />
                  </LineChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>

            {/* Vulnerability Distribution */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <PieChart className="w-5 h-5" />
                  Açık Dağılımı
                </CardTitle>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <PieChart>
                    <Pie
                      data={vulnerabilityDistribution}
                      cx="50%"
                      cy="50%"
                      outerRadius={80}
                      dataKey="value"
                      label={({ name, value }) => `${name}: ${value}`}
                    >
                      {vulnerabilityDistribution.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.color} />
                      ))}
                    </Pie>
                    <RechartsTooltip />
                  </PieChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>
          </div>

          {/* Recent Activities */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Activity className="w-5 h-5" />
                Son Güvenlik Aktiviteleri
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {securityEvents.slice(0, 5).map((event) => (
                  <div key={event.id} className="flex items-center gap-4 p-3 border rounded-lg">
                    <div className={`w-3 h-3 rounded-full ${
                      event.severity === 'CRITICAL' ? 'bg-red-500' :
                      event.severity === 'HIGH' ? 'bg-orange-500' :
                      event.severity === 'MEDIUM' ? 'bg-yellow-500' : 'bg-green-500'
                    }`} />
                    <div className="flex-1">
                      <div className="flex items-center gap-2">
                        <span className="font-medium">{event.description}</span>
                        <Badge variant="secondary" className="text-xs">
                          {event.type}
                        </Badge>
                      </div>
                      <p className="text-sm text-gray-500">
                        {event.source} → {event.target} • {event.timestamp.toLocaleString('tr-TR')}
                      </p>
                    </div>
                    <Badge 
                      variant={event.status === 'RESOLVED' ? 'default' : 'destructive'}
                      className="text-xs"
                    >
                      {event.status}
                    </Badge>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Vulnerabilities Tab */}
        <TabsContent value="vulnerabilities" className="space-y-6">
          {/* Filters */}
          <Card>
            <CardContent className="p-4">
              <div className="flex flex-wrap items-center gap-4">
                <div className="flex items-center gap-2">
                  <Filter className="w-4 h-4" />
                  <span className="text-sm font-medium">Filtreler:</span>
                </div>
                
                <Select value={filters.severity} onValueChange={(value) => setFilters(prev => ({ ...prev, severity: value }))}>
                  <SelectTrigger className="w-32">
                    <SelectValue placeholder="Ciddiyet" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="ALL">Tümü</SelectItem>
                    <SelectItem value="CRITICAL">Kritik</SelectItem>
                    <SelectItem value="HIGH">Yüksek</SelectItem>
                    <SelectItem value="MEDIUM">Orta</SelectItem>
                    <SelectItem value="LOW">Düşük</SelectItem>
                  </SelectContent>
                </Select>

                <Select value={filters.status} onValueChange={(value) => setFilters(prev => ({ ...prev, status: value }))}>
                  <SelectTrigger className="w-32">
                    <SelectValue placeholder="Durum" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="ALL">Tümü</SelectItem>
                    <SelectItem value="OPEN">Açık</SelectItem>
                    <SelectItem value="IN_PROGRESS">Devam Eden</SelectItem>
                    <SelectItem value="RESOLVED">Çözümlendi</SelectItem>
                  </SelectContent>
                </Select>

                <span className="text-sm text-gray-500">
                  {filteredVulnerabilities.length} sonuç gösteriliyor
                </span>
              </div>
            </CardContent>
          </Card>

          {/* Vulnerabilities Table */}
          <Card>
            <CardHeader>
              <CardTitle>Güvenlik Açıkları</CardTitle>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Başlık</TableHead>
                    <TableHead>Ciddiyet</TableHead>
                    <TableHead>Tür</TableHead>
                    <TableHead>Durum</TableHead>
                    <TableHead>Risk Skoru</TableHead>
                    <TableHead>Keşfedilme</TableHead>
                    <TableHead>İşlemler</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredVulnerabilities.slice(0, 10).map((vuln) => (
                    <TableRow key={vuln.id}>
                      <TableCell className="font-medium">{vuln.title}</TableCell>
                      <TableCell>
                        <Badge 
                          variant={vuln.severity === 'CRITICAL' ? 'destructive' : 'secondary'}
                          className={`${
                            vuln.severity === 'CRITICAL' ? 'bg-red-100 text-red-800' :
                            vuln.severity === 'HIGH' ? 'bg-orange-100 text-orange-800' :
                            vuln.severity === 'MEDIUM' ? 'bg-yellow-100 text-yellow-800' :
                            'bg-green-100 text-green-800'
                          }`}
                        >
                          {vuln.severity}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          {vuln.type === 'DEPENDENCY_VULNERABILITY' && <Database className="w-4 h-4" />}
                          {vuln.type === 'CODE_INJECTION' && <Code className="w-4 h-4" />}
                          {vuln.type === 'NETWORK_EXPOSURE' && <Network className="w-4 h-4" />}
                          <span className="text-sm">{vuln.type.replace('_', ' ')}</span>
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge variant={vuln.status === 'RESOLVED' ? 'default' : 'secondary'}>
                          {vuln.status}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          <div className={`w-2 h-2 rounded-full ${
                            vuln.riskScore >= 70 ? 'bg-red-500' :
                            vuln.riskScore >= 40 ? 'bg-yellow-500' : 'bg-green-500'
                          }`} />
                          {vuln.riskScore}
                        </div>
                      </TableCell>
                      <TableCell>{vuln.discoveredAt.toLocaleDateString('tr-TR')}</TableCell>
                      <TableCell>
                        <div className="flex items-center gap-2">
                          <Dialog>
                            <DialogTrigger asChild>
                              <Button variant="outline" size="sm" onClick={() => setSelectedVulnerability(vuln)}>
                                <Eye className="w-4 h-4" />
                              </Button>
                            </DialogTrigger>
                            <DialogContent className="max-w-4xl">
                              <DialogHeader>
                                <DialogTitle>{vuln.title}</DialogTitle>
                              </DialogHeader>
                              <VulnerabilityDetails vulnerability={vuln} onStatusUpdate={handleUpdateVulnerabilityStatus} />
                            </DialogContent>
                          </Dialog>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Threats Tab */}
        <TabsContent value="threats" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <ShieldAlert className="w-5 h-5" />
                Tehdit İstihbaratı
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {threats.map((threat) => (
                  <div key={threat.id} className="border rounded-lg p-4">
                    <div className="flex items-center justify-between mb-3">
                      <div className="flex items-center gap-3">
                        <Badge 
                          variant={threat.severity === 'CRITICAL' ? 'destructive' : 'secondary'}
                          className="font-medium"
                        >
                          {threat.severity}
                        </Badge>
                        <h3 className="font-semibold">{threat.type.replace('_', ' ')}</h3>
                      </div>
                      <span className="text-sm text-gray-500">
                        {threat.timestamp.toLocaleString('tr-TR')}
                      </span>
                    </div>
                    
                    <p className="text-gray-700 mb-3">{threat.description}</p>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                      <div>
                        <strong>Kaynak:</strong> {threat.source}
                      </div>
                      <div>
                        <strong>Etkilenen:</strong> {threat.affected.toLocaleString('tr-TR')} sistem
                      </div>
                      <div className="md:col-span-2">
                        <strong>Göstergeler:</strong> {threat.indicators.join(', ')}
                      </div>
                      <div className="md:col-span-2">
                        <strong>Önlem:</strong> {threat.mitigation}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Events Tab */}
        <TabsContent value="events" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Activity className="w-5 h-5" />
                Güvenlik Olayları
              </CardTitle>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Zaman</TableHead>
                    <TableHead>Tür</TableHead>
                    <TableHead>Ciddiyet</TableHead>
                    <TableHead>Kaynak</TableHead>
                    <TableHead>Hedef</TableHead>
                    <TableHead>Açıklama</TableHead>
                    <TableHead>Durum</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {securityEvents.map((event) => (
                    <TableRow key={event.id}>
                      <TableCell>{event.timestamp.toLocaleString('tr-TR')}</TableCell>
                      <TableCell>
                        <Badge variant="outline">{event.type}</Badge>
                      </TableCell>
                      <TableCell>
                        <Badge 
                          variant={event.severity === 'CRITICAL' ? 'destructive' : 'secondary'}
                        >
                          {event.severity}
                        </Badge>
                      </TableCell>
                      <TableCell className="font-mono text-sm">{event.source}</TableCell>
                      <TableCell className="font-mono text-sm">{event.target}</TableCell>
                      <TableCell>{event.description}</TableCell>
                      <TableCell>
                        <Badge 
                          variant={event.status === 'RESOLVED' ? 'default' : 'secondary'}
                        >
                          {event.status}
                        </Badge>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Compliance Tab */}
        <TabsContent value="compliance" className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle>Uyumluluk Skorları</CardTitle>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <BarChart data={complianceData}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="name" />
                    <YAxis domain={[0, 100]} />
                    <RechartsTooltip />
                    <Bar dataKey="score" fill="#3b82f6" />
                  </BarChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Uyumluluk Gereksinimleri</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  {complianceData.map((compliance) => (
                    <div key={compliance.name} className="flex items-center justify-between p-3 border rounded-lg">
                      <div>
                        <h4 className="font-medium">{compliance.name}</h4>
                        <p className="text-sm text-gray-500">Uyumluluk Standardı</p>
                      </div>
                      <div className="text-right">
                        <div className="text-lg font-bold" style={{ color: compliance.color }}>
                          {compliance.score}%
                        </div>
                        <Progress value={compliance.score} className="w-20 mt-1" />
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        {/* Reports Tab */}
        <TabsContent value="reports" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Güvenlik Raporları</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Button className="h-24 flex-col" onClick={handleExportReport}>
                  <Download className="w-6 h-6 mb-2" />
                  Detaylı Rapor
                </Button>
                <Button variant="outline" className="h-24 flex-col">
                  <FileText className="w-6 h-6 mb-2" />
                  Uyumluluk Raporu
                </Button>
                <Button variant="outline" className="h-24 flex-col">
                  <TrendingUp className="w-6 h-6 mb-2" />
                  Trend Analizi
                </Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
};

// Vulnerability Details Component
const VulnerabilityDetails: React.FC<{
  vulnerability: Vulnerability;
  onStatusUpdate: (id: string, status: Vulnerability['status']) => void;
}> = ({ vulnerability, onStatusUpdate }) => {
  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <Badge 
          variant={vulnerability.severity === 'CRITICAL' ? 'destructive' : 'secondary'}
          className="text-sm font-bold"
        >
          {vulnerability.severity}
        </Badge>
        <Select 
          value={vulnerability.status} 
          onValueChange={(value) => onStatusUpdate(vulnerability.id, value as Vulnerability['status'])}
        >
          <SelectTrigger className="w-40">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="OPEN">Açık</SelectItem>
            <SelectItem value="IN_PROGRESS">Devam Eden</SelectItem>
            <SelectItem value="RESOLVED">Çözümlendi</SelectItem>
            <SelectItem value="FALSE_POSITIVE">Yanlış Pozitif</SelectItem>
          </SelectContent>
        </Select>
      </div>

      {/* Description */}
      <div>
        <h4 className="font-semibold mb-2">Açıklama</h4>
        <p className="text-gray-700">{vulnerability.description}</p>
      </div>

      {/* Affected Components */}
      <div>
        <h4 className="font-semibold mb-2">Etkilenen Bileşenler</h4>
        <div className="space-y-2">
          {vulnerability.affected.map((component, index) => (
            <div key={index} className="flex items-center gap-3 p-2 bg-gray-50 rounded">
              <Badge variant="outline">{component.type}</Badge>
              <span className="font-medium">{component.name}</span>
              {component.version && <span className="text-sm text-gray-500">v{component.version}</span>}
              <span className="text-sm text-gray-500">{component.location}</span>
            </div>
          ))}
        </div>
      </div>

      {/* Remediation Steps */}
      <div>
        <h4 className="font-semibold mb-2">Çözüm Adımları</h4>
        <div className="space-y-3">
          {vulnerability.remediation.map((action, index) => (
            <div key={index} className="border rounded-lg p-4">
              <div className="flex items-center justify-between mb-2">
                <Badge variant="outline">{action.type}</Badge>
                <Badge variant={action.priority === 'IMMEDIATE' ? 'destructive' : 'secondary'}>
                  {action.priority}
                </Badge>
              </div>
              <p className="font-medium mb-2">{action.description}</p>
              <div className="text-sm text-gray-600 space-y-1">
                <p><strong>Tahmini Süre:</strong> {action.estimatedTime}</p>
                <p><strong>Maliyet:</strong> {action.cost}</p>
                <p><strong>Risk Azaltma:</strong> %{action.riskReduction}</p>
              </div>
              <div className="mt-3">
                <strong className="text-sm">Adımlar:</strong>
                <ol className="list-decimal list-inside mt-1 text-sm text-gray-600">
                  {action.steps.map((step, stepIndex) => (
                    <li key={stepIndex}>{step}</li>
                  ))}
                </ol>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Evidence */}
      {vulnerability.evidence.length > 0 && (
        <div>
          <h4 className="font-semibold mb-2">Kanıtlar</h4>
          <div className="space-y-2">
            {vulnerability.evidence.map((evidence, index) => (
              <div key={index} className="border rounded p-3">
                <div className="flex items-center gap-2 mb-2">
                  <Badge variant="outline">{evidence.type}</Badge>
                  <span className="text-sm text-gray-500">{evidence.source}</span>
                </div>
                <p className="text-sm font-medium mb-1">{evidence.description}</p>
                <pre className="text-xs bg-gray-50 p-2 rounded overflow-x-auto">
                  {evidence.data}
                </pre>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default SecurityDashboard; 