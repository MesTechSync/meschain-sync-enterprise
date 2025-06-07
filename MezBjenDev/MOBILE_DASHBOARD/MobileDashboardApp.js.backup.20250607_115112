/**
 * MezBjen Phase 3 - Mobile-First Dashboard Architecture
 * ATOM-M007 Phase 2 Implementation
 * Cross-Platform Mobile Dashboard with Native iOS/Android Apps and PWA
 * 
 * @version 3.0.0
 * @author MezBjen Development Team
 * @created 2024-12-19
 */

import React, { useState, useEffect, useContext, createContext } from 'react';
import { 
    View, 
    Text, 
    ScrollView, 
    RefreshControl, 
    Dimensions, 
    Platform,
    StyleSheet,
    SafeAreaView,
    StatusBar,
    Alert
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import NetInfo from '@react-native-netinfo/netinfo';
import { LineChart, PieChart, BarChart } from 'react-native-chart-kit';
import * as Notifications from 'expo-notifications';
import * as SecureStore from 'expo-secure-store';
import { WebView } from 'react-native-webview';

// Global Configuration
const APP_CONFIG = {
    version: '3.0.0',
    api_base_url: 'https://api.mezbjen.com/v3',
    websocket_url: 'wss://realtime.mezbjen.com',
    offline_sync_interval: 30000, // 30 seconds
    chart_refresh_interval: 15000, // 15 seconds
    push_notification_enabled: true,
    biometric_auth_enabled: true,
    dark_mode_default: false,
    supported_languages: ['en', 'tr', 'ar', 'fr', 'de', 'es'],
    max_offline_data_age: 86400000, // 24 hours
    chart_animation_duration: 1000
};

// Theme Configuration
const THEMES = {
    light: {
        primary: '#1e3a8a',
        secondary: '#3b82f6',
        success: '#10b981',
        warning: '#f59e0b',
        error: '#ef4444',
        background: '#ffffff',
        surface: '#f8fafc',
        text: '#1f2937',
        textSecondary: '#6b7280',
        border: '#e5e7eb',
        shadow: 'rgba(0, 0, 0, 0.1)'
    },
    dark: {
        primary: '#3b82f6',
        secondary: '#60a5fa',
        success: '#34d399',
        warning: '#fbbf24',
        error: '#f87171',
        background: '#111827',
        surface: '#1f2937',
        text: '#f9fafb',
        textSecondary: '#d1d5db',
        border: '#374151',
        shadow: 'rgba(0, 0, 0, 0.3)'
    }
};

// Context for Global State Management
const DashboardContext = createContext();

// Dashboard Data Service
class DashboardDataService {
    constructor() {
        this.apiBaseUrl = APP_CONFIG.api_base_url;
        this.websocketUrl = APP_CONFIG.websocket_url;
        this.websocket = null;
        this.offlineQueue = [];
        this.isOnline = true;
        this.syncInProgress = false;
        
        this.initializeNetworkMonitoring();
        this.initializeWebSocket();
    }
    
    initializeNetworkMonitoring() {
        NetInfo.addEventListener(state => {
            const wasOnline = this.isOnline;
            this.isOnline = state.isConnected && state.isInternetReachable;
            
            if (!wasOnline && this.isOnline) {
                this.syncOfflineData();
            }
        });
    }
    
    initializeWebSocket() {
        if (this.websocket) {
            this.websocket.close();
        }
        
        try {
            this.websocket = new WebSocket(this.websocketUrl);
            
            this.websocket.onopen = () => {
                console.log('WebSocket connected');
                this.authenticateWebSocket();
            };
            
            this.websocket.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.handleRealtimeUpdate(data);
            };
            
            this.websocket.onclose = () => {
                console.log('WebSocket disconnected');
                setTimeout(() => this.initializeWebSocket(), 5000);
            };
            
            this.websocket.onerror = (error) => {
                console.error('WebSocket error:', error);
            };
        } catch (error) {
            console.error('Failed to initialize WebSocket:', error);
        }
    }
    
    async authenticateWebSocket() {
        const token = await SecureStore.getItemAsync('auth_token');
        if (token && this.websocket.readyState === WebSocket.OPEN) {
            this.websocket.send(JSON.stringify({
                type: 'authenticate',
                token: token
            }));
        }
    }
    
    handleRealtimeUpdate(data) {
        // Broadcast real-time updates to dashboard components
        if (this.onRealtimeUpdate) {
            this.onRealtimeUpdate(data);
        }
    }
    
    async fetchDashboardData(endpoint, params = {}) {
        try {
            if (!this.isOnline) {
                return await this.getOfflineData(endpoint, params);
            }
            
            const token = await SecureStore.getItemAsync('auth_token');
            const queryString = new URLSearchParams(params).toString();
            const url = `${this.apiBaseUrl}${endpoint}${queryString ? '?' + queryString : ''}`;
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'X-App-Version': APP_CONFIG.version,
                    'X-Platform': Platform.OS
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            // Cache data for offline use
            await this.cacheData(endpoint, params, data);
            
            return data;
        } catch (error) {
            console.error('API fetch error:', error);
            
            if (!this.isOnline) {
                return await this.getOfflineData(endpoint, params);
            }
            
            throw error;
        }
    }
    
    async cacheData(endpoint, params, data) {
        try {
            const cacheKey = `cache_${endpoint}_${JSON.stringify(params)}`;
            const cacheData = {
                data: data,
                timestamp: Date.now(),
                endpoint: endpoint,
                params: params
            };
            
            await AsyncStorage.setItem(cacheKey, JSON.stringify(cacheData));
        } catch (error) {
            console.error('Cache error:', error);
        }
    }
    
    async getOfflineData(endpoint, params) {
        try {
            const cacheKey = `cache_${endpoint}_${JSON.stringify(params)}`;
            const cachedData = await AsyncStorage.getItem(cacheKey);
            
            if (cachedData) {
                const parsed = JSON.parse(cachedData);
                const age = Date.now() - parsed.timestamp;
                
                if (age < APP_CONFIG.max_offline_data_age) {
                    return parsed.data;
                }
            }
            
            // Return empty data structure if no valid cache
            return this.getEmptyDataStructure(endpoint);
        } catch (error) {
            console.error('Offline data error:', error);
            return this.getEmptyDataStructure(endpoint);
        }
    }
    
    getEmptyDataStructure(endpoint) {
        const structures = {
            '/dashboard/overview': {
                revenue: { current: 0, previous: 0, change: 0 },
                orders: { current: 0, previous: 0, change: 0 },
                customers: { current: 0, previous: 0, change: 0 },
                conversion: { current: 0, previous: 0, change: 0 }
            },
            '/dashboard/analytics': {
                chartData: [],
                metrics: {},
                trends: []
            },
            '/dashboard/realtime': {
                activeUsers: 0,
                recentOrders: [],
                systemStatus: 'offline'
            }
        };
        
        return structures[endpoint] || {};
    }
    
    async syncOfflineData() {
        if (this.syncInProgress || !this.isOnline) return;
        
        this.syncInProgress = true;
        
        try {
            // Process offline queue
            for (const item of this.offlineQueue) {
                await this.processOfflineItem(item);
            }
            
            this.offlineQueue = [];
            console.log('Offline data synced successfully');
        } catch (error) {
            console.error('Offline sync error:', error);
        } finally {
            this.syncInProgress = false;
        }
    }
    
    async processOfflineItem(item) {
        // Process individual offline items
        // This would handle POST requests, user actions, etc.
        try {
            const response = await fetch(item.url, item.options);
            return response.json();
        } catch (error) {
            console.error('Failed to process offline item:', error);
        }
    }
    
    addToOfflineQueue(url, options) {
        this.offlineQueue.push({
            url: url,
            options: options,
            timestamp: Date.now()
        });
    }
}

// Dashboard Context Provider
const DashboardProvider = ({ children }) => {
    const [dashboardData, setDashboardData] = useState({});
    const [isLoading, setIsLoading] = useState(true);
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [theme, setTheme] = useState(THEMES.light);
    const [isDarkMode, setIsDarkMode] = useState(false);
    const [isOnline, setIsOnline] = useState(true);
    const [notifications, setNotifications] = useState([]);
    
    const dataService = new DashboardDataService();
    
    useEffect(() => {
        initializeDashboard();
    }, []);
    
    const initializeDashboard = async () => {
        try {
            // Load theme preference
            const themePreference = await AsyncStorage.getItem('theme_preference');
            if (themePreference === 'dark') {
                setIsDarkMode(true);
                setTheme(THEMES.dark);
            }
            
            // Load initial dashboard data
            await refreshDashboardData();
            
            // Setup real-time updates
            dataService.onRealtimeUpdate = handleRealtimeUpdate;
            
            // Setup periodic refresh
            setupPeriodicRefresh();
            
        } catch (error) {
            console.error('Dashboard initialization error:', error);
        } finally {
            setIsLoading(false);
        }
    };
    
    const refreshDashboardData = async () => {
        try {
            setIsRefreshing(true);
            
            const [overview, analytics, realtime] = await Promise.all([
                dataService.fetchDashboardData('/dashboard/overview'),
                dataService.fetchDashboardData('/dashboard/analytics'),
                dataService.fetchDashboardData('/dashboard/realtime')
            ]);
            
            setDashboardData({
                overview,
                analytics,
                realtime,
                lastUpdated: new Date()
            });
            
        } catch (error) {
            console.error('Dashboard refresh error:', error);
            Alert.alert('Error', 'Failed to refresh dashboard data');
        } finally {
            setIsRefreshing(false);
        }
    };
    
    const handleRealtimeUpdate = (data) => {
        setDashboardData(prevData => ({
            ...prevData,
            realtime: {
                ...prevData.realtime,
                ...data
            },
            lastUpdated: new Date()
        }));
    };
    
    const setupPeriodicRefresh = () => {
        setInterval(() => {
            if (!isRefreshing) {
                refreshDashboardData();
            }
        }, APP_CONFIG.chart_refresh_interval);
    };
    
    const toggleTheme = async () => {
        const newIsDarkMode = !isDarkMode;
        setIsDarkMode(newIsDarkMode);
        setTheme(newIsDarkMode ? THEMES.dark : THEMES.light);
        await AsyncStorage.setItem('theme_preference', newIsDarkMode ? 'dark' : 'light');
    };
    
    const value = {
        dashboardData,
        isLoading,
        isRefreshing,
        theme,
        isDarkMode,
        isOnline,
        notifications,
        refreshDashboardData,
        toggleTheme,
        dataService
    };
    
    return (
        <DashboardContext.Provider value={value}>
            {children}
        </DashboardContext.Provider>
    );
};

// Custom Hooks
const useDashboard = () => {
    const context = useContext(DashboardContext);
    if (!context) {
        throw new Error('useDashboard must be used within a DashboardProvider');
    }
    return context;
};

// Dashboard Components

// Main Dashboard Screen
const DashboardScreen = () => {
    const { 
        dashboardData, 
        isLoading, 
        isRefreshing, 
        theme, 
        refreshDashboardData 
    } = useDashboard();
    
    const screenData = Dimensions.get('window');
    
    if (isLoading) {
        return (
            <SafeAreaView style={[styles.container, { backgroundColor: theme.background }]}>
                <View style={styles.loadingContainer}>
                    <Text style={[styles.loadingText, { color: theme.text }]}>
                        Loading Dashboard...
                    </Text>
                </View>
            </SafeAreaView>
        );
    }
    
    return (
        <SafeAreaView style={[styles.container, { backgroundColor: theme.background }]}>
            <StatusBar 
                barStyle={theme === THEMES.dark ? 'light-content' : 'dark-content'}
                backgroundColor={theme.background}
            />
            
            <ScrollView
                style={styles.scrollView}
                refreshControl={
                    <RefreshControl
                        refreshing={isRefreshing}
                        onRefresh={refreshDashboardData}
                        tintColor={theme.primary}
                    />
                }
            >
                <DashboardHeader />
                <MetricsOverview />
                <ChartSection />
                <RealtimeSection />
                <NotificationSection />
            </ScrollView>
        </SafeAreaView>
    );
};

// Dashboard Header Component
const DashboardHeader = () => {
    const { theme, toggleTheme, dashboardData } = useDashboard();
    
    return (
        <View style={[styles.header, { backgroundColor: theme.surface }]}>
            <View style={styles.headerContent}>
                <Text style={[styles.headerTitle, { color: theme.text }]}>
                    MezBjen Dashboard
                </Text>
                <Text style={[styles.headerSubtitle, { color: theme.textSecondary }]}>
                    {dashboardData.lastUpdated ? 
                        `Last updated: ${dashboardData.lastUpdated.toLocaleTimeString()}` :
                        'Loading...'
                    }
                </Text>
            </View>
            
            <View style={styles.headerActions}>
                <TouchableOpacity 
                    style={[styles.themeToggle, { borderColor: theme.border }]}
                    onPress={toggleTheme}
                >
                    <Text style={[styles.themeToggleText, { color: theme.text }]}>
                        🌙
                    </Text>
                </TouchableOpacity>
            </View>
        </View>
    );
};

// Metrics Overview Component
const MetricsOverview = () => {
    const { theme, dashboardData } = useDashboard();
    const overview = dashboardData.overview || {};
    
    const metrics = [
        {
            title: 'Revenue',
            current: overview.revenue?.current || 0,
            previous: overview.revenue?.previous || 0,
            change: overview.revenue?.change || 0,
            format: 'currency',
            icon: '💰'
        },
        {
            title: 'Orders',
            current: overview.orders?.current || 0,
            previous: overview.orders?.previous || 0,
            change: overview.orders?.change || 0,
            format: 'number',
            icon: '📦'
        },
        {
            title: 'Customers',
            current: overview.customers?.current || 0,
            previous: overview.customers?.previous || 0,
            change: overview.customers?.change || 0,
            format: 'number',
            icon: '👥'
        },
        {
            title: 'Conversion',
            current: overview.conversion?.current || 0,
            previous: overview.conversion?.previous || 0,
            change: overview.conversion?.change || 0,
            format: 'percentage',
            icon: '📈'
        }
    ];
    
    return (
        <View style={styles.metricsContainer}>
            {metrics.map((metric, index) => (
                <MetricCard key={index} metric={metric} theme={theme} />
            ))}
        </View>
    );
};

// Individual Metric Card Component
const MetricCard = ({ metric, theme }) => {
    const formatValue = (value, format) => {
        switch (format) {
            case 'currency':
                return `$${value.toLocaleString()}`;
            case 'percentage':
                return `${value.toFixed(1)}%`;
            case 'number':
            default:
                return value.toLocaleString();
        }
    };
    
    const getChangeColor = (change) => {
        if (change > 0) return theme.success;
        if (change < 0) return theme.error;
        return theme.textSecondary;
    };
    
    const getChangeIcon = (change) => {
        if (change > 0) return '↗️';
        if (change < 0) return '↘️';
        return '→';
    };
    
    return (
        <View style={[styles.metricCard, { backgroundColor: theme.surface, borderColor: theme.border }]}>
            <View style={styles.metricHeader}>
                <Text style={styles.metricIcon}>{metric.icon}</Text>
                <Text style={[styles.metricTitle, { color: theme.textSecondary }]}>
                    {metric.title}
                </Text>
            </View>
            
            <Text style={[styles.metricValue, { color: theme.text }]}>
                {formatValue(metric.current, metric.format)}
            </Text>
            
            <View style={styles.metricChange}>
                <Text style={[styles.metricChangeText, { color: getChangeColor(metric.change) }]}>
                    {getChangeIcon(metric.change)} {Math.abs(metric.change).toFixed(1)}%
                </Text>
            </View>
        </View>
    );
};

// Chart Section Component
const ChartSection = () => {
    const { theme, dashboardData } = useDashboard();
    const analytics = dashboardData.analytics || {};
    const screenData = Dimensions.get('window');
    
    // Sample chart data - replace with real data from analytics
    const revenueData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            data: [120000, 145000, 132000, 178000, 165000, 192000]
        }]
    };
    
    const categoryData = [
        { name: 'Electronics', population: 35, color: theme.primary, legendFontColor: theme.text },
        { name: 'Clothing', population: 28, color: theme.secondary, legendFontColor: theme.text },
        { name: 'Books', population: 20, color: theme.success, legendFontColor: theme.text },
        { name: 'Home', population: 17, color: theme.warning, legendFontColor: theme.text }
    ];
    
    const chartConfig = {
        backgroundColor: theme.surface,
        backgroundGradientFrom: theme.surface,
        backgroundGradientTo: theme.surface,
        decimalPlaces: 0,
        color: (opacity = 1) => `rgba(59, 130, 246, ${opacity})`,
        labelColor: (opacity = 1) => theme.text,
        style: {
            borderRadius: 16
        },
        propsForDots: {
            r: '6',
            strokeWidth: '2',
            stroke: theme.primary
        }
    };
    
    return (
        <View style={styles.chartSection}>
            <Text style={[styles.sectionTitle, { color: theme.text }]}>
                Analytics Overview
            </Text>
            
            <View style={[styles.chartContainer, { backgroundColor: theme.surface }]}>
                <Text style={[styles.chartTitle, { color: theme.text }]}>
                    Revenue Trend
                </Text>
                <LineChart
                    data={revenueData}
                    width={screenData.width - 40}
                    height={220}
                    chartConfig={chartConfig}
                    bezier
                    style={styles.chart}
                />
            </View>
            
            <View style={[styles.chartContainer, { backgroundColor: theme.surface }]}>
                <Text style={[styles.chartTitle, { color: theme.text }]}>
                    Sales by Category
                </Text>
                <PieChart
                    data={categoryData}
                    width={screenData.width - 40}
                    height={220}
                    chartConfig={chartConfig}
                    accessor="population"
                    backgroundColor="transparent"
                    paddingLeft="15"
                    style={styles.chart}
                />
            </View>
        </View>
    );
};

// Real-time Section Component
const RealtimeSection = () => {
    const { theme, dashboardData } = useDashboard();
    const realtime = dashboardData.realtime || {};
    
    return (
        <View style={styles.realtimeSection}>
            <Text style={[styles.sectionTitle, { color: theme.text }]}>
                Real-time Activity
            </Text>
            
            <View style={[styles.realtimeContainer, { backgroundColor: theme.surface }]}>
                <View style={styles.realtimeMetric}>
                    <Text style={[styles.realtimeLabel, { color: theme.textSecondary }]}>
                        Active Users
                    </Text>
                    <Text style={[styles.realtimeValue, { color: theme.success }]}>
                        {realtime.activeUsers || 0} 🟢
                    </Text>
                </View>
                
                <View style={styles.realtimeMetric}>
                    <Text style={[styles.realtimeLabel, { color: theme.textSecondary }]}>
                        System Status
                    </Text>
                    <Text style={[styles.realtimeValue, { color: realtime.systemStatus === 'online' ? theme.success : theme.error }]}>
                        {realtime.systemStatus || 'offline'} {realtime.systemStatus === 'online' ? '✅' : '❌'}
                    </Text>
                </View>
            </View>
            
            <View style={[styles.recentOrdersContainer, { backgroundColor: theme.surface }]}>
                <Text style={[styles.subsectionTitle, { color: theme.text }]}>
                    Recent Orders
                </Text>
                {(realtime.recentOrders || []).map((order, index) => (
                    <View key={index} style={[styles.orderItem, { borderBottomColor: theme.border }]}>
                        <Text style={[styles.orderText, { color: theme.text }]}>
                            Order #{order.id} - ${order.amount}
                        </Text>
                        <Text style={[styles.orderTime, { color: theme.textSecondary }]}>
                            {order.time}
                        </Text>
                    </View>
                ))}
            </View>
        </View>
    );
};

// Notification Section Component
const NotificationSection = () => {
    const { theme, notifications } = useDashboard();
    
    if (!notifications || notifications.length === 0) {
        return null;
    }
    
    return (
        <View style={styles.notificationSection}>
            <Text style={[styles.sectionTitle, { color: theme.text }]}>
                Notifications
            </Text>
            
            {notifications.map((notification, index) => (
                <View key={index} style={[styles.notificationItem, { backgroundColor: theme.surface, borderColor: theme.border }]}>
                    <Text style={[styles.notificationText, { color: theme.text }]}>
                        {notification.message}
                    </Text>
                    <Text style={[styles.notificationTime, { color: theme.textSecondary }]}>
                        {notification.time}
                    </Text>
                </View>
            ))}
        </View>
    );
};

// Styles
const styles = StyleSheet.create({
    container: {
        flex: 1
    },
    loadingContainer: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center'
    },
    loadingText: {
        fontSize: 18,
        fontWeight: '600'
    },
    scrollView: {
        flex: 1
    },
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: 20,
        borderBottomWidth: 1
    },
    headerContent: {
        flex: 1
    },
    headerTitle: {
        fontSize: 24,
        fontWeight: 'bold'
    },
    headerSubtitle: {
        fontSize: 14,
        marginTop: 4
    },
    headerActions: {
        flexDirection: 'row'
    },
    themeToggle: {
        width: 40,
        height: 40,
        borderRadius: 20,
        borderWidth: 1,
        justifyContent: 'center',
        alignItems: 'center'
    },
    themeToggleText: {
        fontSize: 18
    },
    metricsContainer: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        padding: 10
    },
    metricCard: {
        width: '48%',
        margin: '1%',
        padding: 16,
        borderRadius: 12,
        borderWidth: 1
    },
    metricHeader: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 8
    },
    metricIcon: {
        fontSize: 20,
        marginRight: 8
    },
    metricTitle: {
        fontSize: 14,
        fontWeight: '500'
    },
    metricValue: {
        fontSize: 24,
        fontWeight: 'bold',
        marginBottom: 8
    },
    metricChange: {
        flexDirection: 'row'
    },
    metricChangeText: {
        fontSize: 12,
        fontWeight: '600'
    },
    chartSection: {
        padding: 20
    },
    sectionTitle: {
        fontSize: 20,
        fontWeight: 'bold',
        marginBottom: 16
    },
    chartContainer: {
        borderRadius: 12,
        padding: 16,
        marginBottom: 16
    },
    chartTitle: {
        fontSize: 16,
        fontWeight: '600',
        marginBottom: 12
    },
    chart: {
        borderRadius: 8
    },
    realtimeSection: {
        padding: 20
    },
    realtimeContainer: {
        borderRadius: 12,
        padding: 16,
        marginBottom: 16
    },
    realtimeMetric: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 8
    },
    realtimeLabel: {
        fontSize: 16
    },
    realtimeValue: {
        fontSize: 16,
        fontWeight: 'bold'
    },
    recentOrdersContainer: {
        borderRadius: 12,
        padding: 16
    },
    subsectionTitle: {
        fontSize: 16,
        fontWeight: '600',
        marginBottom: 12
    },
    orderItem: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 8,
        borderBottomWidth: 1
    },
    orderText: {
        fontSize: 14
    },
    orderTime: {
        fontSize: 12
    },
    notificationSection: {
        padding: 20,
        paddingBottom: 40
    },
    notificationItem: {
        borderRadius: 8,
        padding: 12,
        marginBottom: 8,
        borderWidth: 1
    },
    notificationText: {
        fontSize: 14,
        marginBottom: 4
    },
    notificationTime: {
        fontSize: 12
    }
});

// Main App Component
const MobileDashboardApp = () => {
    return (
        <DashboardProvider>
            <DashboardScreen />
        </DashboardProvider>
    );
};

export default MobileDashboardApp;
export { DashboardProvider, useDashboard, DashboardDataService };
