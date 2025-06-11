Compiled with problems:
×
ERROR in ./src/App.tsx 9:0-42
Module not found: Error: Can't resolve 'react-hot-toast' in '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/src'
ERROR in ./src/App.tsx 21:0-52
Module not found: Error: Can't resolve './modules/ProductModule' in '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/src'
ERROR in ./src/App.tsx 22:0-48
Module not found: Error: Can't resolve './modules/OrderModule' in '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/src'
ERROR in ./src/App.tsx 23:0-56
Module not found: Error: Can't resolve './modules/InventoryModule' in '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/src'
ERROR in ./src/App.tsx 24:0-50
Module not found: Error: Can't resolve './modules/ReportModule' in '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/src'
ERROR in ./src/App.tsx 25:0-54
Module not found: Error: Can't resolve './modules/SettingsModule' in '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/src'
ERROR in ./src/i18n/index.ts 5:0-64
Module not found: Error: Can't resolve 'i18next-browser-languagedetector' in '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/src/i18n'
ERROR in ./src/i18n/index.ts 6:0-43
Module not found: Error: Can't resolve 'i18next-http-backend' in '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/src/i18n'
ERROR in src/App.tsx:6:25
TS2307: Cannot find module 'react-hot-toast' or its corresponding type declarations.
    4 | import { CssBaseline, CircularProgress, Box } from '@mui/material';
    5 | import { ErrorBoundary } from 'react-error-boundary';
  > 6 | import { Toaster } from 'react-hot-toast';
      |                         ^^^^^^^^^^^^^^^^^
    7 | import { I18nextProvider } from 'react-i18next';
    8 |
    9 | import { theme } from './theme/theme';
ERROR in src/App.tsx:19:27
TS2307: Cannot find module './modules/ProductModule' or its corresponding type declarations.
    17 | import EbayModule from './modules/EbayModule';
    18 | import OzonModule from './modules/OzonModule';
  > 19 | import ProductModule from './modules/ProductModule';
       |                           ^^^^^^^^^^^^^^^^^^^^^^^^^
    20 | import OrderModule from './modules/OrderModule';
    21 | import InventoryModule from './modules/InventoryModule';
    22 | import ReportModule from './modules/ReportModule';
ERROR in src/App.tsx:20:25
TS2307: Cannot find module './modules/OrderModule' or its corresponding type declarations.
    18 | import OzonModule from './modules/OzonModule';
    19 | import ProductModule from './modules/ProductModule';
  > 20 | import OrderModule from './modules/OrderModule';
       |                         ^^^^^^^^^^^^^^^^^^^^^^^
    21 | import InventoryModule from './modules/InventoryModule';
    22 | import ReportModule from './modules/ReportModule';
    23 | import SettingsModule from './modules/SettingsModule';
ERROR in src/App.tsx:21:29
TS2307: Cannot find module './modules/InventoryModule' or its corresponding type declarations.
    19 | import ProductModule from './modules/ProductModule';
    20 | import OrderModule from './modules/OrderModule';
  > 21 | import InventoryModule from './modules/InventoryModule';
       |                             ^^^^^^^^^^^^^^^^^^^^^^^^^^^
    22 | import ReportModule from './modules/ReportModule';
    23 | import SettingsModule from './modules/SettingsModule';
    24 | import i18n from './i18n';
ERROR in src/App.tsx:22:26
TS2307: Cannot find module './modules/ReportModule' or its corresponding type declarations.
    20 | import OrderModule from './modules/OrderModule';
    21 | import InventoryModule from './modules/InventoryModule';
  > 22 | import ReportModule from './modules/ReportModule';
       |                          ^^^^^^^^^^^^^^^^^^^^^^^^
    23 | import SettingsModule from './modules/SettingsModule';
    24 | import i18n from './i18n';
    25 | // AI/ML Integration - Priority 2
ERROR in src/App.tsx:23:28
TS2307: Cannot find module './modules/SettingsModule' or its corresponding type declarations.
    21 | import InventoryModule from './modules/InventoryModule';
    22 | import ReportModule from './modules/ReportModule';
  > 23 | import SettingsModule from './modules/SettingsModule';
       |                            ^^^^^^^^^^^^^^^^^^^^^^^^^^
    24 | import i18n from './i18n';
    25 | // AI/ML Integration - Priority 2
    26 | import AdvancedAIIntegration from './ai/AdvancedAIIntegration';
ERROR in src/App.tsx:62:41
TS2741: Property 'children' is missing in type '{}' but required in type 'AppLayoutProps'.
    60 |           <div className="App">
    61 |             <Routes>
  > 62 |               <Route path="/" element={<AppLayout />}>
       |                                         ^^^^^^^^^
    63 |                 <Route index element={<MainDashboard />} />
    64 |                 <Route path="dashboard" element={<MainDashboard />} />
    65 |                 
ERROR in src/components/analytics/AdvancedAnalytics.tsx:38:8
TS2307: Cannot find module '@heroicons/react/24/outline' or its corresponding type declarations.
    36 |   DocumentArrowDownIcon,
    37 |   Cog6ToothIcon
  > 38 | } from '@heroicons/react/24/outline';
       |        ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    39 | import { format, subDays, startOfDay, endOfDay, isWithinInterval } from 'date-fns';
    40 | import { tr, enUS } from 'date-fns/locale';
    41 | import _ from 'lodash';
ERROR in src/components/analytics/AdvancedAnalytics.tsx:42:19
TS2307: Cannot find module 'react-hot-toast' or its corresponding type declarations.
    40 | import { tr, enUS } from 'date-fns/locale';
    41 | import _ from 'lodash';
  > 42 | import toast from 'react-hot-toast';
       |                   ^^^^^^^^^^^^^^^^^
    43 | import {
    44 |   Box,
    45 |   Card,
ERROR in src/components/analytics/AnalyticsFilters.tsx:12:8
TS2307: Cannot find module '@heroicons/react/24/outline' or its corresponding type declarations.
    10 |   AdjustmentsHorizontalIcon,
    11 |   ArrowPathIcon
  > 12 | } from '@heroicons/react/24/outline';
       |        ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    13 | import { format, subDays, startOfMonth, endOfMonth, startOfYear, endOfYear } from 'date-fns';
    14 | import { tr, enUS } from 'date-fns/locale';
    15 |
ERROR in src/components/Animations/AnimationProvider.tsx:526:6
TS2786: 'AnimatePresence' cannot be used as a JSX component.
  Its return type 'Element | undefined' is not a valid JSX element.
    Type 'undefined' is not assignable to type 'Element | null'.
    524 |
    525 |   return (
  > 526 |     <AnimatePresence>
        |      ^^^^^^^^^^^^^^^
    527 |       {isOpen && (
    528 |         <>
    529 |           {/* Backdrop */}
ERROR in src/components/CategoryMapping/CategoryTreeVisualization.tsx:355:61
TS2339: Property 'borderRadius' does not exist on type '{ input: { base: { padding: string; borderRadius: string; border: string; fontSize: string; fontFamily: string; transition: string; backgroundColor: string; }; focus: { borderColor: string; boxShadow: string; outline: string; }; error: { ...; }; success: { ...; }; }; label: { ...; }; }'.
    353 |           padding: `${MS365Spacing[2]} ${MS365Spacing[3]}`,
    354 |           border: `1px solid ${MS365Colors.neutral[300]}`,
  > 355 |           borderRadius: AdvancedMS365Theme.components.forms.borderRadius,
        |                                                             ^^^^^^^^^^^^
    356 |           fontSize: MS365Typography.sizes.sm,
    357 |           fontFamily: MS365Typography.fonts.system,
    358 |           minWidth: '200px',
ERROR in src/components/CategoryMapping/CategoryTreeVisualization.tsx:370:61
TS2339: Property 'borderRadius' does not exist on type '{ input: { base: { padding: string; borderRadius: string; border: string; fontSize: string; fontFamily: string; transition: string; backgroundColor: string; }; focus: { borderColor: string; boxShadow: string; outline: string; }; error: { ...; }; success: { ...; }; }; label: { ...; }; }'.
    368 |           padding: `${MS365Spacing[2]} ${MS365Spacing[3]}`,
    369 |           border: `1px solid ${MS365Colors.neutral[300]}`,
  > 370 |           borderRadius: AdvancedMS365Theme.components.forms.borderRadius,
        |                                                             ^^^^^^^^^^^^
    371 |           fontSize: MS365Typography.sizes.sm,
    372 |           fontFamily: MS365Typography.fonts.system,
    373 |           backgroundColor: MS365Colors.background.primary,
ERROR in src/components/CategoryMapping/CategoryTreeVisualization.tsx:594:50
TS2345: Argument of type '(prev: TreeViewState) => { filterBy: string; selectedNodes: string[]; expandedNodes: string[]; hoveredNode: string | null; draggedNode: string | null; searchQuery: string; viewMode: "flow" | ... 1 more ... | "hierarchy"; }' is not assignable to parameter of type 'SetStateAction<TreeViewState>'.
  Type '(prev: TreeViewState) => { filterBy: string; selectedNodes: string[]; expandedNodes: string[]; hoveredNode: string | null; draggedNode: string | null; searchQuery: string; viewMode: "flow" | ... 1 more ... | "hierarchy"; }' is not assignable to type '(prevState: TreeViewState) => TreeViewState'.
    Call signature return types '{ filterBy: string; selectedNodes: string[]; expandedNodes: string[]; hoveredNode: string | null; draggedNode: string | null; searchQuery: string; viewMode: "flow" | "tree" | "hierarchy"; }' and 'TreeViewState' are incompatible.
      The types of 'filterBy' are incompatible between these types.
        Type 'string' is not assignable to type '"all" | "pending" | "mapped" | "unmapped"'.
    592 |         filterBy={treeState.filterBy}
    593 |         onSearchChange={(query) => setTreeState(prev => ({ ...prev, searchQuery: query }))}
  > 594 |         onFilterChange={(filter) => setTreeState(prev => ({ ...prev, filterBy: filter }))}
        |                                                  ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    595 |         onExpandAll={handleExpandAll}
    596 |         onCollapseAll={handleCollapseAll}
    597 |       />
ERROR in src/components/Dashboard.tsx:14:8
TS2307: Cannot find module '@heroicons/react/24/outline' or its corresponding type declarations.
    12 |   XCircleIcon,
    13 |   ClockIcon
  > 14 | } from '@heroicons/react/24/outline';
       |        ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    15 | import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar } from 'recharts';
    16 | import { Microsoft365Theme, getAcademicColorScheme, academicComponentStyles } from '../theme/microsoft365';
    17 |
ERROR in src/components/Dashboard.tsx:154:21
TS2367: This comparison appears to be unintentional because the types '"neutral"' and '"decrease"' have no overlap.
    152 |                   <div className={`ml-2 flex items-center text-sm ${
    153 |                     stat.changeType === 'increase' ? 'text-green-600' : 
  > 154 |                     stat.changeType === 'decrease' ? 'text-red-600' : 'text-gray-600'
        |                     ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    155 |                   }`}>
    156 |                     {stat.changeType === 'increase' && <ArrowUpIcon className="w-4 h-4 mr-1" />}
    157 |                     {stat.changeType === 'decrease' && <ArrowDownIcon className="w-4 h-4 mr-1" />}
ERROR in src/components/Dashboard.tsx:157:22
TS2367: This comparison appears to be unintentional because the types '"increase" | "neutral"' and '"decrease"' have no overlap.
    155 |                   }`}>
    156 |                     {stat.changeType === 'increase' && <ArrowUpIcon className="w-4 h-4 mr-1" />}
  > 157 |                     {stat.changeType === 'decrease' && <ArrowDownIcon className="w-4 h-4 mr-1" />}
        |                      ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    158 |                     <span>{stat.change}</span>
    159 |                   </div>
    160 |                 </div>
ERROR in src/components/Dashboard/MetricCard/MetricCard.test.tsx:6:37
TS2307: Cannot find module '../../../tests/utils/testUtils' or its corresponding type declarations.
    4 | import React from 'react';
    5 | import { screen, fireEvent, waitFor } from '@testing-library/react';
  > 6 | import { renderWithProviders } from '../../../tests/utils/testUtils';
      |                                     ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    7 | import { MetricCard } from './MetricCard';
    8 | import { TrendingUp, TrendingDown, TrendingFlat } from '@mui/icons-material';
    9 |
ERROR in src/components/Dashboard/MetricCard/MetricCard.test.tsx:7:28
TS2307: Cannot find module './MetricCard' or its corresponding type declarations.
     5 | import { screen, fireEvent, waitFor } from '@testing-library/react';
     6 | import { renderWithProviders } from '../../../tests/utils/testUtils';
  >  7 | import { MetricCard } from './MetricCard';
       |                            ^^^^^^^^^^^^^^
     8 | import { TrendingUp, TrendingDown, TrendingFlat } from '@mui/icons-material';
     9 |
    10 | // ====================================
ERROR in src/components/DragDrop/DragDropProvider.tsx:106:42
TS2802: Type 'Set<string>' can only be iterated through when using the '--downlevelIteration' flag or with a '--target' of 'es2015' or higher.
    104 |
    105 |   const addSelectedItem = useCallback((id: string) => {
  > 106 |     setSelectedItems(prev => new Set([...prev, id]));
        |                                          ^^^^
    107 |   }, []);
    108 |
    109 |   const removeSelectedItem = useCallback((id: string) => {
ERROR in src/components/DragDrop/DragDropProvider.tsx:683:12
TS2339: Property 'isDragging' does not exist on type 'unknown'.
    681 |   const ref = useRef<HTMLDivElement>(null);
    682 |
  > 683 |   const [{ isDragging }, drag] = useDrag({
        |            ^^^^^^^^^^
    684 |     type: 'grid-item',
    685 |     item: { index },
    686 |     begin: onDragStart,
ERROR in src/components/DragDrop/DragDropProvider.tsx:686:5
TS2345: Argument of type '{ type: string; item: { index: number; }; begin: () => void; end: () => void; collect: (monitor: DragSourceMonitor<{ index: number; }, unknown>) => { isDragging: boolean; }; }' is not assignable to parameter of type 'FactoryOrInstance<DragSourceHookSpec<{ index: number; }, unknown, { isDragging: boolean; }>>'.
  Object literal may only specify known properties, and 'begin' does not exist in type 'FactoryOrInstance<DragSourceHookSpec<{ index: number; }, unknown, { isDragging: boolean; }>>'.
    684 |     type: 'grid-item',
    685 |     item: { index },
  > 686 |     begin: onDragStart,
        |     ^^^^^^^^^^^^^^^^^^
    687 |     end: onDragEnd,
    688 |     collect: (monitor) => ({
    689 |       isDragging: monitor.isDragging()
ERROR in src/components/LanguageManager.tsx:11:8
TS2307: Cannot find module '@heroicons/react/24/outline' or its corresponding type declarations.
     9 |   InformationCircleIcon,
    10 |   ArrowPathIcon
  > 11 | } from '@heroicons/react/24/outline';
       |        ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    12 |
    13 | interface LanguageSettings {
    14 |   defaultLanguage: string;
ERROR in src/components/LanguageSwitcher.tsx:3:58
TS2307: Cannot find module '@heroicons/react/24/outline' or its corresponding type declarations.
    1 | import React from 'react';
    2 | import { useTranslation } from 'react-i18next';
  > 3 | import { ChevronDownIcon, GlobeAltIcon, CheckIcon } from '@heroicons/react/24/outline';
      |                                                          ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    4 | import { useLanguage } from '../hooks/useLanguage';
    5 |
    6 | interface Language {
ERROR in src/components/LanguageSwitcher/AdvancedLanguageSwitcher.tsx:276:55
TS2339: Property 'borderRadius' does not exist on type '{ input: { base: { padding: string; borderRadius: string; border: string; fontSize: string; fontFamily: string; transition: string; backgroundColor: string; }; focus: { borderColor: string; boxShadow: string; outline: string; }; error: { ...; }; success: { ...; }; }; label: { ...; }; }'.
    274 |     padding: size === 'sm' ? MS365Spacing[1] : size === 'lg' ? MS365Spacing[3] : MS365Spacing[2],
    275 |     border: `1px solid ${MS365Colors.neutral[300]}`,
  > 276 |     borderRadius: AdvancedMS365Theme.components.forms.borderRadius,
        |                                                       ^^^^^^^^^^^^
    277 |     backgroundColor: MS365Colors.background.primary,
    278 |     cursor: 'pointer',
    279 |     fontSize: size === 'sm' ? MS365Typography.sizes.sm : MS365Typography.sizes.base,
ERROR in src/components/LanguageSwitcher/AdvancedLanguageSwitcher.tsx:292:35
TS2339: Property 'shadows' does not exist on type '{ components: { buttons: { variants: { primary: { backgroundColor: string; color: string; border: string; hover: { backgroundColor: string; transform: string; boxShadow: string; }; active: { backgroundColor: string; transform: string; }; disabled: { ...; }; }; secondary: { ...; }; ghost: { ...; }; destructive: { ......'.
    290 |     border: `1px solid ${MS365Colors.neutral[300]}`,
    291 |     borderRadius: AdvancedMS365Theme.components.cards.radiuses.md,
  > 292 |     boxShadow: AdvancedMS365Theme.shadows.medium,
        |                                   ^^^^^^^
    293 |     zIndex: 1000,
    294 |     maxHeight: '300px',
    295 |     overflowY: 'auto'
ERROR in src/components/LanguageSwitcher/AdvancedLanguageSwitcher.tsx:362:35
TS2339: Property 'shadows' does not exist on type '{ components: { buttons: { variants: { primary: { backgroundColor: string; color: string; border: string; hover: { backgroundColor: string; transform: string; boxShadow: string; }; active: { backgroundColor: string; transform: string; }; disabled: { ...; }; }; secondary: { ...; }; ghost: { ...; }; destructive: { ......'.
    360 |     backgroundColor: MS365Colors.background.primary,
    361 |     borderRadius: AdvancedMS365Theme.components.cards.radiuses.lg,
  > 362 |     boxShadow: AdvancedMS365Theme.shadows.large,
        |                                   ^^^^^^^
    363 |     maxWidth: '500px',
    364 |     width: '90%',
    365 |     maxHeight: '80vh',
ERROR in src/components/Layout.tsx:5:146
TS2307: Cannot find module '@heroicons/react/24/outline' or its corresponding type declarations.
    3 | import { useTranslation } from 'react-i18next';
    4 | import LanguageSwitcher from './LanguageSwitcher';
  > 5 | import { HomeIcon, ChartBarIcon, BuildingStorefrontIcon, TruckIcon, ShoppingCartIcon, ArchiveBoxIcon, DocumentChartBarIcon, Cog6ToothIcon } from '@heroicons/react/24/outline';
      |                                                                                                                                                  ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    6 | import { NavLink } from 'react-router-dom';
    7 |
    8 | interface User {
ERROR in src/components/Microsoft365/MS365Button.tsx:271:48
TS2339: Property 'borderColor' does not exist on type '{ backgroundColor: string; } | { backgroundColor: string; color: string; textDecoration: string; }'.
  Property 'borderColor' does not exist on type '{ backgroundColor: string; }'.
    269 |             Object.assign(e.currentTarget.style, {
    270 |               backgroundColor: variantStyles.hover.backgroundColor,
  > 271 |               borderColor: variantStyles.hover.borderColor,
        |                                                ^^^^^^^^^^^
    272 |               color: variantStyles.hover.color,
    273 |               transform: variantStyles.hover.transform || 'translateY(-1px)',
    274 |               boxShadow: variantStyles.hover.boxShadow || 'none'
ERROR in src/components/Microsoft365/MS365Button.tsx:272:42
TS2339: Property 'color' does not exist on type '{ backgroundColor: string; } | { backgroundColor: string; color: string; textDecoration: string; }'.
  Property 'color' does not exist on type '{ backgroundColor: string; }'.
    270 |               backgroundColor: variantStyles.hover.backgroundColor,
    271 |               borderColor: variantStyles.hover.borderColor,
  > 272 |               color: variantStyles.hover.color,
        |                                          ^^^^^
    273 |               transform: variantStyles.hover.transform || 'translateY(-1px)',
    274 |               boxShadow: variantStyles.hover.boxShadow || 'none'
    275 |             });
ERROR in src/components/Microsoft365/MS365Button.tsx:273:46
TS2339: Property 'transform' does not exist on type '{ backgroundColor: string; } | { backgroundColor: string; color: string; textDecoration: string; }'.
  Property 'transform' does not exist on type '{ backgroundColor: string; }'.
    271 |               borderColor: variantStyles.hover.borderColor,
    272 |               color: variantStyles.hover.color,
  > 273 |               transform: variantStyles.hover.transform || 'translateY(-1px)',
        |                                              ^^^^^^^^^
    274 |               boxShadow: variantStyles.hover.boxShadow || 'none'
    275 |             });
    276 |           }
ERROR in src/components/Microsoft365/MS365Button.tsx:274:46
TS2339: Property 'boxShadow' does not exist on type '{ backgroundColor: string; } | { backgroundColor: string; color: string; textDecoration: string; }'.
  Property 'boxShadow' does not exist on type '{ backgroundColor: string; }'.
    272 |               color: variantStyles.hover.color,
    273 |               transform: variantStyles.hover.transform || 'translateY(-1px)',
  > 274 |               boxShadow: variantStyles.hover.boxShadow || 'none'
        |                                              ^^^^^^^^^
    275 |             });
    276 |           }
    277 |         }}
ERROR in src/components/Microsoft365/MS365Dashboard.tsx:274:9
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    272 |         title="🎯 AI Category Mapping Suggestions"
    273 |         subtitle="Review and approve AI-suggested category mappings"
  > 274 |         size="lg"
        |         ^^^^
    275 |       >
    276 |         <div style={mappingGridStyle}>
    277 |           {mappings.map((mapping) => (
ERROR in src/components/Microsoft365/MS365Dashboard.tsx:331:23
TS2322: Type '"xs"' is not assignable to type '"md" | "sm" | "lg" | "xl" | undefined'.
    329 |                   <div style={{ display: 'flex', gap: Microsoft365DesignSystem.spacing[1] }}>
    330 |                     <MS365Button
  > 331 |                       size="xs"
        |                       ^^^^
    332 |                       variant="success"
    333 |                       onClick={(e) => {
    334 |                         e.stopPropagation();
ERROR in src/components/Microsoft365/MS365Dashboard.tsx:332:23
TS2322: Type '"success"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    330 |                     <MS365Button
    331 |                       size="xs"
  > 332 |                       variant="success"
        |                       ^^^^^^^
    333 |                       onClick={(e) => {
    334 |                         e.stopPropagation();
    335 |                         approveMapping(mapping.id);
ERROR in src/components/Microsoft365/MS365Dashboard.tsx:341:23
TS2322: Type '"xs"' is not assignable to type '"md" | "sm" | "lg" | "xl" | undefined'.
    339 |                     </MS365Button>
    340 |                     <MS365Button
  > 341 |                       size="xs"
        |                       ^^^^
    342 |                       variant="error"
    343 |                       onClick={(e) => {
    344 |                         e.stopPropagation();
ERROR in src/components/Microsoft365/MS365Dashboard.tsx:342:23
TS2322: Type '"error"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    340 |                     <MS365Button
    341 |                       size="xs"
  > 342 |                       variant="error"
        |                       ^^^^^^^
    343 |                       onClick={(e) => {
    344 |                         e.stopPropagation();
    345 |                         rejectMapping(mapping.id);
ERROR in src/components/Microsoft365/MS365Dashboard.tsx:351:23
TS2322: Type '"xs"' is not assignable to type '"md" | "sm" | "lg" | "xl" | undefined'.
    349 |                     </MS365Button>
    350 |                     <MS365Button
  > 351 |                       size="xs"
        |                       ^^^^
    352 |                       variant="secondary"
    353 |                       onClick={(e) => {
    354 |                         e.stopPropagation();
ERROR in src/components/Microsoft365/MS365Forms.tsx:192:55
TS2339: Property 'borderRadius' does not exist on type '{ input: { base: { padding: string; borderRadius: string; border: string; fontSize: string; fontFamily: string; transition: string; backgroundColor: string; }; focus: { borderColor: string; boxShadow: string; outline: string; }; error: { ...; }; success: { ...; }; }; label: { ...; }; }'.
    190 |     width: fullWidth ? '100%' : 'auto',
    191 |     border: `2px solid ${error ? MS365Colors.primary.red[400] : isFocused ? MS365Colors.primary.blue[500] : MS365Colors.neutral[300]}`,
  > 192 |     borderRadius: AdvancedMS365Theme.components.forms.borderRadius,
        |                                                       ^^^^^^^^^^^^
    193 |     backgroundColor: disabled ? MS365Colors.neutral[100] : MS365Colors.background.primary,
    194 |     color: disabled ? MS365Colors.neutral[500] : MS365Colors.neutral[900],
    195 |     fontFamily: MS365Typography.fonts.system,
ERROR in src/components/Microsoft365/MS365Forms.tsx:602:65
TS2339: Property 'borderRadius' does not exist on type '{ input: { base: { padding: string; borderRadius: string; border: string; fontSize: string; fontFamily: string; transition: string; backgroundColor: string; }; focus: { borderColor: string; boxShadow: string; outline: string; }; error: { ...; }; success: { ...; }; }; label: { ...; }; }'.
    600 |               padding: `${MS365Spacing[3]} ${MS365Spacing[6]}`,
    601 |               border: `2px solid ${MS365Colors.neutral[300]}`,
  > 602 |               borderRadius: AdvancedMS365Theme.components.forms.borderRadius,
        |                                                                 ^^^^^^^^^^^^
    603 |               backgroundColor: 'transparent',
    604 |               color: MS365Colors.neutral[700],
    605 |               fontSize: MS365Typography.sizes.base,
ERROR in src/components/Microsoft365/MS365Forms.tsx:627:63
TS2339: Property 'borderRadius' does not exist on type '{ input: { base: { padding: string; borderRadius: string; border: string; fontSize: string; fontFamily: string; transition: string; backgroundColor: string; }; focus: { borderColor: string; boxShadow: string; outline: string; }; error: { ...; }; success: { ...; }; }; label: { ...; }; }'.
    625 |             padding: `${MS365Spacing[3]} ${MS365Spacing[6]}`,
    626 |             border: 'none',
  > 627 |             borderRadius: AdvancedMS365Theme.components.forms.borderRadius,
        |                                                               ^^^^^^^^^^^^
    628 |             backgroundColor: formState.isValid ? MS365Colors.primary.blue[500] : MS365Colors.neutral[400],
    629 |             color: MS365Colors.neutral[50],
    630 |             fontSize: MS365Typography.sizes.base,
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:319:9
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    317 |         title="👥 Rol Dağılımı"
    318 |         subtitle="Kullanıcıların rol bazında dağılımı"
  > 319 |         size="lg"
        |         ^^^^
    320 |       >
    321 |         <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: Microsoft365DesignSystem.spacing[4] }}>
    322 |           {[
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:362:40
TS2322: Type '{ children: string; variant: "primary"; leftIcon: string; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    360 |           🎛️ Panel Sistemleri
    361 |         </h2>
  > 362 |         <MS365Button variant="primary" leftIcon="➕">
        |                                        ^^^^^^^^
    363 |           Yeni Panel Oluştur
    364 |         </MS365Button>
    365 |       </div>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:453:9
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    451 |       <MS365Card
    452 |         title="🔍 Gelişmiş Kullanıcı Filtreleme"
  > 453 |         size="lg"
        |         ^^^^
    454 |       >
    455 |         <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: Microsoft365DesignSystem.spacing[3] }}>
    456 |           <MS365Button variant="ghost" leftIcon="👑" fullWidth>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:456:40
TS2322: Type '{ children: (string | number)[]; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    454 |       >
    455 |         <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: Microsoft365DesignSystem.spacing[3] }}>
  > 456 |           <MS365Button variant="ghost" leftIcon="👑" fullWidth>
        |                                        ^^^^^^^^
    457 |             Super Admin ({stats.superAdmins})
    458 |           </MS365Button>
    459 |           <MS365Button variant="ghost" leftIcon="👨‍💼" fullWidth>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:459:40
TS2322: Type '{ children: (string | number)[]; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    457 |             Super Admin ({stats.superAdmins})
    458 |           </MS365Button>
  > 459 |           <MS365Button variant="ghost" leftIcon="👨‍💼" fullWidth>
        |                                        ^^^^^^^^
    460 |             Admin ({stats.admins})
    461 |           </MS365Button>
    462 |           <MS365Button variant="ghost" leftIcon="🔧" fullWidth>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:462:40
TS2322: Type '{ children: (string | number)[]; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    460 |             Admin ({stats.admins})
    461 |           </MS365Button>
  > 462 |           <MS365Button variant="ghost" leftIcon="🔧" fullWidth>
        |                                        ^^^^^^^^
    463 |             Integrator ({stats.integrators})
    464 |           </MS365Button>
    465 |           <MS365Button variant="ghost" leftIcon="📦" fullWidth>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:465:40
TS2322: Type '{ children: (string | number)[]; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    463 |             Integrator ({stats.integrators})
    464 |           </MS365Button>
  > 465 |           <MS365Button variant="ghost" leftIcon="📦" fullWidth>
        |                                        ^^^^^^^^
    466 |             Dropshipper ({stats.dropshippers})
    467 |           </MS365Button>
    468 |           <MS365Button variant="ghost" leftIcon="🎧" fullWidth>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:468:40
TS2322: Type '{ children: (string | number)[]; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    466 |             Dropshipper ({stats.dropshippers})
    467 |           </MS365Button>
  > 468 |           <MS365Button variant="ghost" leftIcon="🎧" fullWidth>
        |                                        ^^^^^^^^
    469 |             Support ({stats.support})
    470 |           </MS365Button>
    471 |           <MS365Button variant="ghost" leftIcon="👁️" fullWidth>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:471:40
TS2322: Type '{ children: (string | number)[]; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    469 |             Support ({stats.support})
    470 |           </MS365Button>
  > 471 |           <MS365Button variant="ghost" leftIcon="👁️" fullWidth>
        |                                        ^^^^^^^^
    472 |             Viewer ({stats.viewers})
    473 |           </MS365Button>
    474 |         </div>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:480:9
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    478 |         title="⚡ Hızlı İşlemler"
    479 |         subtitle="Toplu kullanıcı yönetimi"
  > 480 |         size="lg"
        |         ^^^^
    481 |       >
    482 |         <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: Microsoft365DesignSystem.spacing[3] }}>
    483 |           <MS365Button variant="primary" leftIcon="👤">
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:483:42
TS2322: Type '{ children: string; variant: "primary"; leftIcon: string; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    481 |       >
    482 |         <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: Microsoft365DesignSystem.spacing[3] }}>
  > 483 |           <MS365Button variant="primary" leftIcon="👤">
        |                                          ^^^^^^^^
    484 |             Yeni Kullanıcı Ekle
    485 |           </MS365Button>
    486 |           <MS365Button variant="secondary" leftIcon="📤">
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:486:44
TS2322: Type '{ children: string; variant: "secondary"; leftIcon: string; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    484 |             Yeni Kullanıcı Ekle
    485 |           </MS365Button>
  > 486 |           <MS365Button variant="secondary" leftIcon="📤">
        |                                            ^^^^^^^^
    487 |             Toplu İçe Aktar
    488 |           </MS365Button>
    489 |           <MS365Button variant="warning" leftIcon="⚠️">
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:489:24
TS2322: Type '"warning"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    487 |             Toplu İçe Aktar
    488 |           </MS365Button>
  > 489 |           <MS365Button variant="warning" leftIcon="⚠️">
        |                        ^^^^^^^
    490 |             Pasif Kullanıcılar
    491 |           </MS365Button>
    492 |           <MS365Button variant="error" leftIcon="🔒">
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:492:24
TS2322: Type '"error"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    490 |             Pasif Kullanıcılar
    491 |           </MS365Button>
  > 492 |           <MS365Button variant="error" leftIcon="🔒">
        |                        ^^^^^^^
    493 |             Güvenlik Taraması
    494 |           </MS365Button>
    495 |           <MS365Button variant="success" leftIcon="✅">
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:495:24
TS2322: Type '"success"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    493 |             Güvenlik Taraması
    494 |           </MS365Button>
  > 495 |           <MS365Button variant="success" leftIcon="✅">
        |                        ^^^^^^^
    496 |             Aktif Kullanıcılar
    497 |           </MS365Button>
    498 |           <MS365Button variant="ghost" leftIcon="📊">
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:498:40
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    496 |             Aktif Kullanıcılar
    497 |           </MS365Button>
  > 498 |           <MS365Button variant="ghost" leftIcon="📊">
        |                                        ^^^^^^^^
    499 |             Kullanıcı Raporları
    500 |           </MS365Button>
    501 |         </div>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:515:9
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    513 |         title="🎯 Rol Bazlı Erişim Kontrolü"
    514 |         subtitle="Panel ve özellik erişim yetkileri"
  > 515 |         size="lg"
        |         ^^^^
    516 |       >
    517 |         <div style={{ overflow: 'auto' }}>
    518 |           <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: Microsoft365DesignSystem.typography.fontSize.sm }}>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:626:52
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    624 |
    625 |       <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: Microsoft365DesignSystem.spacing[6] }}>
  > 626 |         <MS365Card title="🔧 Genel Panel Ayarları" size="lg">
        |                                                    ^^^^
    627 |           <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
    628 |             <MS365Button variant="ghost" leftIcon="🎨" fullWidth>
    629 |               Panel Tema Ayarları
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:628:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    626 |         <MS365Card title="🔧 Genel Panel Ayarları" size="lg">
    627 |           <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
  > 628 |             <MS365Button variant="ghost" leftIcon="🎨" fullWidth>
        |                                          ^^^^^^^^
    629 |               Panel Tema Ayarları
    630 |             </MS365Button>
    631 |             <MS365Button variant="ghost" leftIcon="⏰" fullWidth>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:631:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    629 |               Panel Tema Ayarları
    630 |             </MS365Button>
  > 631 |             <MS365Button variant="ghost" leftIcon="⏰" fullWidth>
        |                                          ^^^^^^^^
    632 |               Oturum Zaman Aşımı
    633 |             </MS365Button>
    634 |             <MS365Button variant="ghost" leftIcon="🔔" fullWidth>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:634:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    632 |               Oturum Zaman Aşımı
    633 |             </MS365Button>
  > 634 |             <MS365Button variant="ghost" leftIcon="🔔" fullWidth>
        |                                          ^^^^^^^^
    635 |               Bildirim Tercihleri
    636 |             </MS365Button>
    637 |             <MS365Button variant="ghost" leftIcon="🌍" fullWidth>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:637:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    635 |               Bildirim Tercihleri
    636 |             </MS365Button>
  > 637 |             <MS365Button variant="ghost" leftIcon="🌍" fullWidth>
        |                                          ^^^^^^^^
    638 |               Dil ve Bölge Ayarları
    639 |             </MS365Button>
    640 |           </div>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:643:49
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    641 |         </MS365Card>
    642 |
  > 643 |         <MS365Card title="🔐 Güvenlik Ayarları" size="lg">
        |                                                 ^^^^
    644 |           <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
    645 |             <MS365Button variant="ghost" leftIcon="🛡️" fullWidth>
    646 |               2FA Zorunluluğu
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:645:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    643 |         <MS365Card title="🔐 Güvenlik Ayarları" size="lg">
    644 |           <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
  > 645 |             <MS365Button variant="ghost" leftIcon="🛡️" fullWidth>
        |                                          ^^^^^^^^
    646 |               2FA Zorunluluğu
    647 |             </MS365Button>
    648 |             <MS365Button variant="ghost" leftIcon="🔑" fullWidth>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:648:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    646 |               2FA Zorunluluğu
    647 |             </MS365Button>
  > 648 |             <MS365Button variant="ghost" leftIcon="🔑" fullWidth>
        |                                          ^^^^^^^^
    649 |               Şifre Politikaları
    650 |             </MS365Button>
    651 |             <MS365Button variant="ghost" leftIcon="📝" fullWidth>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:651:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    649 |               Şifre Politikaları
    650 |             </MS365Button>
  > 651 |             <MS365Button variant="ghost" leftIcon="📝" fullWidth>
        |                                          ^^^^^^^^
    652 |               Audit Log Ayarları
    653 |             </MS365Button>
    654 |             <MS365Button variant="warning" leftIcon="⚠️" fullWidth>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:654:26
TS2322: Type '"warning"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    652 |               Audit Log Ayarları
    653 |             </MS365Button>
  > 654 |             <MS365Button variant="warning" leftIcon="⚠️" fullWidth>
        |                          ^^^^^^^
    655 |               Güvenlik Taraması
    656 |             </MS365Button>
    657 |           </div>
ERROR in src/components/Microsoft365/MS365PanelManager.tsx:689:13
TS2322: Type '{ children: string; key: string; variant: "primary" | "ghost"; leftIcon: string; onClick: () => void; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    687 |             key={tab.key}
    688 |             variant={activeTab === tab.key ? 'primary' : 'ghost'}
  > 689 |             leftIcon={tab.icon}
        |             ^^^^^^^^
    690 |             onClick={() => setActiveTab(tab.key as any)}
    691 |           >
    692 |             {tab.label}
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:304:9
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    302 |         title="⚡ Hızlı İşlemler"
    303 |         subtitle="Sık kullanılan admin fonksiyonları"
  > 304 |         size="lg"
        |         ^^^^
    305 |       >
    306 |         <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: Microsoft365DesignSystem.spacing[3] }}>
    307 |           <MS365Button variant="primary" leftIcon="👤" onClick={() => setActiveTab('users')}>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:307:42
TS2322: Type '{ children: string; variant: "primary"; leftIcon: string; onClick: () => void; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    305 |       >
    306 |         <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: Microsoft365DesignSystem.spacing[3] }}>
  > 307 |           <MS365Button variant="primary" leftIcon="👤" onClick={() => setActiveTab('users')}>
        |                                          ^^^^^^^^
    308 |             Kullanıcı Yönetimi
    309 |           </MS365Button>
    310 |           <MS365Button variant="secondary" leftIcon="🔐" onClick={() => setActiveTab('security')}>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:310:44
TS2322: Type '{ children: string; variant: "secondary"; leftIcon: string; onClick: () => void; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    308 |             Kullanıcı Yönetimi
    309 |           </MS365Button>
  > 310 |           <MS365Button variant="secondary" leftIcon="🔐" onClick={() => setActiveTab('security')}>
        |                                            ^^^^^^^^
    311 |             Güvenlik Paneli
    312 |           </MS365Button>
    313 |           <MS365Button variant="success" leftIcon="🏪" onClick={() => setActiveTab('marketplaces')}>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:313:24
TS2322: Type '"success"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    311 |             Güvenlik Paneli
    312 |           </MS365Button>
  > 313 |           <MS365Button variant="success" leftIcon="🏪" onClick={() => setActiveTab('marketplaces')}>
        |                        ^^^^^^^
    314 |             Pazaryeri Ayarları
    315 |           </MS365Button>
    316 |           <MS365Button variant="warning" leftIcon="📋" onClick={() => setActiveTab('logs')}>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:316:24
TS2322: Type '"warning"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    314 |             Pazaryeri Ayarları
    315 |           </MS365Button>
  > 316 |           <MS365Button variant="warning" leftIcon="📋" onClick={() => setActiveTab('logs')}>
        |                        ^^^^^^^
    317 |             Sistem Logları
    318 |           </MS365Button>
    319 |           <MS365Button variant="ghost" leftIcon="⚙️" onClick={() => setActiveTab('settings')}>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:319:40
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; onClick: () => void; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    317 |             Sistem Logları
    318 |           </MS365Button>
  > 319 |           <MS365Button variant="ghost" leftIcon="⚙️" onClick={() => setActiveTab('settings')}>
        |                                        ^^^^^^^^
    320 |             Sistem Ayarları
    321 |           </MS365Button>
    322 |           <MS365Button variant="error" leftIcon="🔄">
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:322:24
TS2322: Type '"error"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    320 |             Sistem Ayarları
    321 |           </MS365Button>
  > 322 |           <MS365Button variant="error" leftIcon="🔄">
        |                        ^^^^^^^
    323 |             Sistem Yeniden Başlat
    324 |           </MS365Button>
    325 |         </div>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:336:40
TS2322: Type '{ children: string; variant: "primary"; leftIcon: string; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    334 |           👥 Kullanıcı Yönetimi
    335 |         </h2>
  > 336 |         <MS365Button variant="primary" leftIcon="➕">
        |                                        ^^^^^^^^
    337 |           Yeni Kullanıcı Ekle
    338 |         </MS365Button>
    339 |       </div>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:341:18
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    339 |       </div>
    340 |
  > 341 |       <MS365Card size="lg">
        |                  ^^^^
    342 |         <div style={{ overflow: 'auto' }}>
    343 |           <table style={tableStyle}>
    344 |             <thead>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:388:36
TS2322: Type '"xs"' is not assignable to type '"md" | "sm" | "lg" | "xl" | undefined'.
    386 |                   <td style={tableCellStyle}>
    387 |                     <div style={{ display: 'flex', gap: Microsoft365DesignSystem.spacing[2] }}>
  > 388 |                       <MS365Button size="xs" variant="ghost" leftIcon="✏️">
        |                                    ^^^^
    389 |                         Düzenle
    390 |                       </MS365Button>
    391 |                       <MS365Button size="xs" variant="ghost" leftIcon="🔒">
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:391:36
TS2322: Type '"xs"' is not assignable to type '"md" | "sm" | "lg" | "xl" | undefined'.
    389 |                         Düzenle
    390 |                       </MS365Button>
  > 391 |                       <MS365Button size="xs" variant="ghost" leftIcon="🔒">
        |                                    ^^^^
    392 |                         Kilitle
    393 |                       </MS365Button>
    394 |                       {user.role !== 'super_admin' && (
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:395:38
TS2322: Type '"xs"' is not assignable to type '"md" | "sm" | "lg" | "xl" | undefined'.
    393 |                       </MS365Button>
    394 |                       {user.role !== 'super_admin' && (
  > 395 |                         <MS365Button size="xs" variant="error" leftIcon="🗑️">
        |                                      ^^^^
    396 |                           Sil
    397 |                         </MS365Button>
    398 |                       )}
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:395:48
TS2322: Type '"error"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    393 |                       </MS365Button>
    394 |                       {user.role !== 'super_admin' && (
  > 395 |                         <MS365Button size="xs" variant="error" leftIcon="🗑️">
        |                                                ^^^^^^^
    396 |                           Sil
    397 |                         </MS365Button>
    398 |                       )}
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:420:9
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    418 |         title="🚨 Güvenlik Uyarıları"
    419 |         subtitle={`${alerts.filter(a => !a.resolved).length} aktif uyarı`}
  > 420 |         size="lg"
        |         ^^^^
    421 |       >
    422 |         <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[3] }}>
    423 |           {alerts.map(alert => (
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:466:32
TS2322: Type '"xs"' is not assignable to type '"md" | "sm" | "lg" | "xl" | undefined'.
    464 |               {!alert.resolved && (
    465 |                 <div style={{ display: 'flex', gap: Microsoft365DesignSystem.spacing[2] }}>
  > 466 |                   <MS365Button size="xs" variant="success" onClick={() => resolveAlert(alert.id)}>
        |                                ^^^^
    467 |                     ✅ Çöz
    468 |                   </MS365Button>
    469 |                   <MS365Button size="xs" variant="ghost">
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:466:42
TS2322: Type '"success"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    464 |               {!alert.resolved && (
    465 |                 <div style={{ display: 'flex', gap: Microsoft365DesignSystem.spacing[2] }}>
  > 466 |                   <MS365Button size="xs" variant="success" onClick={() => resolveAlert(alert.id)}>
        |                                          ^^^^^^^
    467 |                     ✅ Çöz
    468 |                   </MS365Button>
    469 |                   <MS365Button size="xs" variant="ghost">
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:469:32
TS2322: Type '"xs"' is not assignable to type '"md" | "sm" | "lg" | "xl" | undefined'.
    467 |                     ✅ Çöz
    468 |                   </MS365Button>
  > 469 |                   <MS365Button size="xs" variant="ghost">
        |                                ^^^^
    470 |                     📋 Detay
    471 |                   </MS365Button>
    472 |                 </div>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:544:19
TS2322: Type '"primary" | "success"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
  Type '"success"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    542 |                 <MS365Button 
    543 |                   size="sm" 
  > 544 |                   variant={marketplace.status === 'active' ? 'success' : 'primary'}
        |                   ^^^^^^^
    545 |                   fullWidth
    546 |                 >
    547 |                   {marketplace.status === 'active' ? '⚙️ Ayarlar' : '🔗 Bağlan'}
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:564:45
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    562 |
    563 |       <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: Microsoft365DesignSystem.spacing[6] }}>
  > 564 |         <MS365Card title="🌐 Genel Ayarlar" size="lg">
        |                                             ^^^^
    565 |           <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
    566 |             <MS365Button variant="ghost" leftIcon="🌍" fullWidth>
    567 |               Dil ve Bölge Ayarları
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:566:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    564 |         <MS365Card title="🌐 Genel Ayarlar" size="lg">
    565 |           <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
  > 566 |             <MS365Button variant="ghost" leftIcon="🌍" fullWidth>
        |                                          ^^^^^^^^
    567 |               Dil ve Bölge Ayarları
    568 |             </MS365Button>
    569 |             <MS365Button variant="ghost" leftIcon="⏰" fullWidth>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:569:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    567 |               Dil ve Bölge Ayarları
    568 |             </MS365Button>
  > 569 |             <MS365Button variant="ghost" leftIcon="⏰" fullWidth>
        |                                          ^^^^^^^^
    570 |               Zaman Dilimi Ayarları
    571 |             </MS365Button>
    572 |             <MS365Button variant="ghost" leftIcon="📧" fullWidth>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:572:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    570 |               Zaman Dilimi Ayarları
    571 |             </MS365Button>
  > 572 |             <MS365Button variant="ghost" leftIcon="📧" fullWidth>
        |                                          ^^^^^^^^
    573 |               Email Konfigürasyonu
    574 |             </MS365Button>
    575 |             <MS365Button variant="ghost" leftIcon="🔔" fullWidth>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:575:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    573 |               Email Konfigürasyonu
    574 |             </MS365Button>
  > 575 |             <MS365Button variant="ghost" leftIcon="🔔" fullWidth>
        |                                          ^^^^^^^^
    576 |               Bildirim Ayarları
    577 |             </MS365Button>
    578 |           </div>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:581:49
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    579 |         </MS365Card>
    580 |
  > 581 |         <MS365Card title="🔐 Güvenlik Ayarları" size="lg">
        |                                                 ^^^^
    582 |           <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
    583 |             <MS365Button variant="ghost" leftIcon="🛡️" fullWidth>
    584 |               Firewall Kuralları
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:583:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    581 |         <MS365Card title="🔐 Güvenlik Ayarları" size="lg">
    582 |           <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
  > 583 |             <MS365Button variant="ghost" leftIcon="🛡️" fullWidth>
        |                                          ^^^^^^^^
    584 |               Firewall Kuralları
    585 |             </MS365Button>
    586 |             <MS365Button variant="ghost" leftIcon="🔑" fullWidth>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:586:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    584 |               Firewall Kuralları
    585 |             </MS365Button>
  > 586 |             <MS365Button variant="ghost" leftIcon="🔑" fullWidth>
        |                                          ^^^^^^^^
    587 |               API Anahtar Yönetimi
    588 |             </MS365Button>
    589 |             <MS365Button variant="ghost" leftIcon="🔐" fullWidth>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:589:42
TS2322: Type '{ children: string; variant: "ghost"; leftIcon: string; fullWidth: true; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    587 |               API Anahtar Yönetimi
    588 |             </MS365Button>
  > 589 |             <MS365Button variant="ghost" leftIcon="🔐" fullWidth>
        |                                          ^^^^^^^^
    590 |               2FA Zorunluluğu
    591 |             </MS365Button>
    592 |             <MS365Button variant="warning" leftIcon="⚠️" fullWidth>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:592:26
TS2322: Type '"warning"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    590 |               2FA Zorunluluğu
    591 |             </MS365Button>
  > 592 |             <MS365Button variant="warning" leftIcon="⚠️" fullWidth>
        |                          ^^^^^^^
    593 |               Güvenlik Logları
    594 |             </MS365Button>
    595 |           </div>
ERROR in src/components/Microsoft365/MS365SuperAdminPanel.tsx:627:13
TS2322: Type '{ children: string; key: string; variant: "primary" | "ghost"; leftIcon: string; onClick: () => void; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    625 |             key={tab.key}
    626 |             variant={activeTab === tab.key ? 'primary' : 'ghost'}
  > 627 |             leftIcon={tab.icon}
        |             ^^^^^^^^
    628 |             onClick={() => setActiveTab(tab.key as any)}
    629 |           >
    630 |             {tab.label}
ERROR in src/components/Mobile/MobileUIComponents.tsx:72:42
TS2345: Argument of type 'React.Touch' is not assignable to parameter of type 'Touch'.
  Type 'Touch' is missing the following properties from type 'Touch': force, radiusX, radiusY, rotationAngle
    70 |     // Handle pinch gesture
    71 |     if (e.touches.length === 2 && onPinchZoom) {
  > 72 |       const distance = calculateDistance(e.touches[0], e.touches[1]);
       |                                          ^^^^^^^^^^^^
    73 |       pinchStartRef.current = { distance };
    74 |     }
    75 |
ERROR in src/components/Mobile/MobileUIComponents.tsx:98:49
TS2345: Argument of type 'React.Touch' is not assignable to parameter of type 'Touch'.
     96 |     if (e.touches.length === 2 && pinchStartRef.current && onPinchZoom) {
     97 |       e.preventDefault();
  >  98 |       const currentDistance = calculateDistance(e.touches[0], e.touches[1]);
        |                                                 ^^^^^^^^^^^^
     99 |       const scale = currentDistance / pinchStartRef.current.distance;
    100 |       onPinchZoom(scale);
    101 |     }
ERROR in src/components/Mobile/MobileUIComponents.tsx:399:75
TS2339: Property 'primary' does not exist on type 'Microsoft365Theme'.
    397 |               }`}
    398 |               style={{
  > 399 |                 backgroundColor: activeTab === tab.id ? Microsoft365Theme.primary.blue : 'transparent'
        |                                                                           ^^^^^^^
    400 |               }}
    401 |             >
    402 |               <div className="relative">
ERROR in src/components/Mobile/MobileUIComponents.tsx:407:65
TS2339: Property 'primary' does not exist on type 'Microsoft365Theme'.
    405 |                   <span 
    406 |                     className="absolute -top-2 -right-2 min-w-5 h-5 text-xs font-bold text-white rounded-full flex items-center justify-center"
  > 407 |                     style={{ backgroundColor: Microsoft365Theme.primary.red }}
        |                                                                 ^^^^^^^
    408 |                   >
    409 |                     {tab.badge > 99 ? '99+' : tab.badge}
    410 |                   </span>
ERROR in src/components/Performance/PerformanceDashboard.tsx:42:8
TS2307: Cannot find module '@/components/ui' or its corresponding type declarations.
    40 |   TooltipProvider,
    41 |   TooltipTrigger
  > 42 | } from '@/components/ui';
       |        ^^^^^^^^^^^^^^^^^
    43 | import {
    44 |   Activity,
    45 |   Zap,
ERROR in src/components/Performance/PerformanceDashboard.tsx:71:8
TS2307: Cannot find module 'lucide-react' or its corresponding type declarations.
    69 |   Users,
    70 |   Server
  > 71 | } from 'lucide-react';
       |        ^^^^^^^^^^^^^^
    72 | import { 
    73 |   LineChart as RechartsLineChart, 
    74 |   Line, 
ERROR in src/components/Performance/PerformanceDashboard.tsx:90:74
TS2307: Cannot find module '../../services/performance/PerformanceMonitor' or its corresponding type declarations.
    88 |   ComposedChart
    89 | } from 'recharts';
  > 90 | import PerformanceMonitor, { PerformanceMetrics, PerformanceAlert } from '../../services/performance/PerformanceMonitor';
       |                                                                          ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    91 | import CacheManager from '../../services/performance/CacheManager';
    92 | import DatabaseOptimizer from '../../services/performance/DatabaseOptimizer';
    93 | import BundleOptimizer from '../../services/performance/BundleOptimizer';
ERROR in src/components/Performance/PerformanceDashboard.tsx:91:26
TS2307: Cannot find module '../../services/performance/CacheManager' or its corresponding type declarations.
    89 | } from 'recharts';
    90 | import PerformanceMonitor, { PerformanceMetrics, PerformanceAlert } from '../../services/performance/PerformanceMonitor';
  > 91 | import CacheManager from '../../services/performance/CacheManager';
       |                          ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    92 | import DatabaseOptimizer from '../../services/performance/DatabaseOptimizer';
    93 | import BundleOptimizer from '../../services/performance/BundleOptimizer';
    94 |
ERROR in src/components/Performance/PerformanceDashboard.tsx:92:31
TS2307: Cannot find module '../../services/performance/DatabaseOptimizer' or its corresponding type declarations.
    90 | import PerformanceMonitor, { PerformanceMetrics, PerformanceAlert } from '../../services/performance/PerformanceMonitor';
    91 | import CacheManager from '../../services/performance/CacheManager';
  > 92 | import DatabaseOptimizer from '../../services/performance/DatabaseOptimizer';
       |                               ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    93 | import BundleOptimizer from '../../services/performance/BundleOptimizer';
    94 |
    95 | // Types
ERROR in src/components/Performance/PerformanceDashboard.tsx:93:29
TS2307: Cannot find module '../../services/performance/BundleOptimizer' or its corresponding type declarations.
    91 | import CacheManager from '../../services/performance/CacheManager';
    92 | import DatabaseOptimizer from '../../services/performance/DatabaseOptimizer';
  > 93 | import BundleOptimizer from '../../services/performance/BundleOptimizer';
       |                             ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    94 |
    95 | // Types
    96 | interface PerformanceOverview {
ERROR in src/components/Priority5/Priority5Dashboard.tsx:13:30
TS2307: Cannot find module '../Microsoft365/MS365Spinner' or its corresponding type declarations.
    11 | import { MS365Card } from '../Microsoft365/MS365Card';
    12 | import { MS365Button } from '../Microsoft365/MS365Button';
  > 13 | import { MS365Spinner } from '../Microsoft365/MS365Spinner';
       |                              ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    14 | import { SecurityProvider } from '../../security/SecurityManager';
    15 |
    16 | // Lazy load components for optimal performance
ERROR in src/components/Priority5/Priority5Dashboard.tsx:52:41
TS2339: Property 'success' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    50 |
    51 |   const getHealthColor = (score: number) => {
  > 52 |     if (score >= 90) return MS365Colors.success;
       |                                         ^^^^^^^
    53 |     if (score >= 70) return MS365Colors.warning;
    54 |     return MS365Colors.error;
    55 |   };
ERROR in src/components/Priority5/Priority5Dashboard.tsx:53:41
TS2339: Property 'warning' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    51 |   const getHealthColor = (score: number) => {
    52 |     if (score >= 90) return MS365Colors.success;
  > 53 |     if (score >= 70) return MS365Colors.warning;
       |                                         ^^^^^^^
    54 |     return MS365Colors.error;
    55 |   };
    56 |
ERROR in src/components/Priority5/Priority5Dashboard.tsx:54:24
TS2339: Property 'error' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    52 |     if (score >= 90) return MS365Colors.success;
    53 |     if (score >= 70) return MS365Colors.warning;
  > 54 |     return MS365Colors.error;
       |                        ^^^^^
    55 |   };
    56 |
    57 |   const getHealthStatus = (securityScore: number, performanceScore: number) => {
ERROR in src/components/Priority5/Priority5Dashboard.tsx:77:37
TS2339: Property 'fontFamily' does not exist on type '{ fonts: { system: string; mono: string; }; sizes: { xs: string; sm: string; base: string; lg: string; xl: string; '2xl': string; '3xl': string; '4xl': string; '5xl': string; }; weights: { light: number; normal: number; medium: number; semibold: number; bold: number; extrabold: number; }; lineHeights: { ...; }; lett...'.
    75 |         minHeight: '100vh',
    76 |         backgroundColor: MS365Colors.background.primary,
  > 77 |         fontFamily: MS365Typography.fontFamily
       |                                     ^^^^^^^^^^
    78 |       }}>
    79 |         {/* Header */}
    80 |         <div style={{
ERROR in src/components/Priority5/Priority5Dashboard.tsx:81:86
TS2339: Property 'info' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    79 |         {/* Header */}
    80 |         <div style={{
  > 81 |           background: `linear-gradient(135deg, ${MS365Colors.primary}, ${MS365Colors.info})`,
       |                                                                                      ^^^^
    82 |           color: 'white',
    83 |           padding: MS365Spacing.xl,
    84 |           marginBottom: MS365Spacing.l
ERROR in src/components/Priority5/Priority5Dashboard.tsx:83:33
TS2339: Property 'xl' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    81 |           background: `linear-gradient(135deg, ${MS365Colors.primary}, ${MS365Colors.info})`,
    82 |           color: 'white',
  > 83 |           padding: MS365Spacing.xl,
       |                                 ^^
    84 |           marginBottom: MS365Spacing.l
    85 |         }}>
    86 |           <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:84:38
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    82 |           color: 'white',
    83 |           padding: MS365Spacing.xl,
  > 84 |           marginBottom: MS365Spacing.l
       |                                      ^
    85 |         }}>
    86 |           <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
    87 |             <h1 style={{ 
ERROR in src/components/Priority5/Priority5Dashboard.tsx:88:34
TS2339: Property 'h1' does not exist on type '{ fonts: { system: string; mono: string; }; sizes: { xs: string; sm: string; base: string; lg: string; xl: string; '2xl': string; '3xl': string; '4xl': string; '5xl': string; }; weights: { light: number; normal: number; medium: number; semibold: number; bold: number; extrabold: number; }; lineHeights: { ...; }; lett...'.
    86 |           <div style={{ maxWidth: '1400px', margin: '0 auto' }}>
    87 |             <h1 style={{ 
  > 88 |               ...MS365Typography.h1, 
       |                                  ^^
    89 |               color: 'white', 
    90 |               margin: 0,
    91 |               marginBottom: MS365Spacing.s 
ERROR in src/components/Priority5/Priority5Dashboard.tsx:91:42
TS2339: Property 's' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    89 |               color: 'white', 
    90 |               margin: 0,
  > 91 |               marginBottom: MS365Spacing.s 
       |                                          ^
    92 |             }}>
    93 |               Priority 5: Performance & Security Center
    94 |             </h1>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:96:34
TS2339: Property 'body' does not exist on type '{ fonts: { system: string; mono: string; }; sizes: { xs: string; sm: string; base: string; lg: string; xl: string; '2xl': string; '3xl': string; '4xl': string; '5xl': string; }; weights: { light: number; normal: number; medium: number; semibold: number; bold: number; extrabold: number; }; lineHeights: { ...; }; lett...'.
    94 |             </h1>
    95 |             <p style={{ 
  > 96 |               ...MS365Typography.body, 
       |                                  ^^^^
    97 |               color: 'rgba(255,255,255,0.9)', 
    98 |               margin: 0,
    99 |               fontSize: '18px'
ERROR in src/components/Priority5/Priority5Dashboard.tsx:107:33
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    105 |             <div style={{ 
    106 |               display: 'flex', 
  > 107 |               gap: MS365Spacing.l, 
        |                                 ^
    108 |               marginTop: MS365Spacing.l,
    109 |               flexWrap: 'wrap'
    110 |             }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:108:39
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    106 |               display: 'flex', 
    107 |               gap: MS365Spacing.l, 
  > 108 |               marginTop: MS365Spacing.l,
        |                                       ^
    109 |               flexWrap: 'wrap'
    110 |             }}>
    111 |               <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing.s }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:111:86
TS2339: Property 's' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    109 |               flexWrap: 'wrap'
    110 |             }}>
  > 111 |               <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing.s }}>
        |                                                                                      ^
    112 |                 <div style={{
    113 |                   width: '12px',
    114 |                   height: '12px',
ERROR in src/components/Priority5/Priority5Dashboard.tsx:121:86
TS2339: Property 's' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    119 |                 <span>Security: {systemStats.securityScore.toFixed(0)}%</span>
    120 |               </div>
  > 121 |               <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing.s }}>
        |                                                                                      ^
    122 |                 <div style={{
    123 |                   width: '12px',
    124 |                   height: '12px',
ERROR in src/components/Priority5/Priority5Dashboard.tsx:131:86
TS2339: Property 's' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    129 |                 <span>Performance: {systemStats.performanceScore.toFixed(0)}%</span>
    130 |               </div>
  > 131 |               <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing.s }}>
        |                                                                                      ^
    132 |                 <span>System Health: </span>
    133 |                 <strong>{getHealthStatus(systemStats.securityScore, systemStats.performanceScore)}</strong>
    134 |               </div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:135:86
TS2339: Property 's' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    133 |                 <strong>{getHealthStatus(systemStats.securityScore, systemStats.performanceScore)}</strong>
    134 |               </div>
  > 135 |               <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing.s }}>
        |                                                                                      ^
    136 |                 <span>Last Scan: {systemStats.lastScan.toLocaleTimeString()}</span>
    137 |               </div>
    138 |             </div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:142:88
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    140 |         </div>
    141 |
  > 142 |         <div style={{ maxWidth: '1400px', margin: '0 auto', padding: `0 ${MS365Spacing.l}` }}>
        |                                                                                        ^
    143 |           {/* Navigation Tabs */}
    144 |           <div style={{ 
    145 |             display: 'flex', 
ERROR in src/components/Priority5/Priority5Dashboard.tsx:146:52
TS2339: Property 'border' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    144 |           <div style={{ 
    145 |             display: 'flex', 
  > 146 |             borderBottom: `2px solid ${MS365Colors.border}`,
        |                                                    ^^^^^^
    147 |             marginBottom: MS365Spacing.l,
    148 |             overflowX: 'auto'
    149 |           }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:147:40
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    145 |             display: 'flex', 
    146 |             borderBottom: `2px solid ${MS365Colors.border}`,
  > 147 |             marginBottom: MS365Spacing.l,
        |                                        ^
    148 |             overflowX: 'auto'
    149 |           }}>
    150 |             {tabs.map(tab => (
ERROR in src/components/Priority5/Priority5Dashboard.tsx:155:19
TS2322: Type 'string | { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; ... 5 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }' is not assignable to type 'Background<string | number> | undefined'.
  Type '{ blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }' is not assignable to type 'Background<string | number> | undefined'.
    153 |                 onClick={() => setActiveTab(tab.key as DashboardTab)}
    154 |                 style={{
  > 155 |                   background: activeTab === tab.key ? MS365Colors.primary : 'transparent',
        |                   ^^^^^^^^^^
    156 |                   color: activeTab === tab.key ? 'white' : MS365Colors.text.primary,
    157 |                   border: 'none',
    158 |                   padding: `${MS365Spacing.m} ${MS365Spacing.l}`,
ERROR in src/components/Priority5/Priority5Dashboard.tsx:156:72
TS2339: Property 'text' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    154 |                 style={{
    155 |                   background: activeTab === tab.key ? MS365Colors.primary : 'transparent',
  > 156 |                   color: activeTab === tab.key ? 'white' : MS365Colors.text.primary,
        |                                                                        ^^^^
    157 |                   border: 'none',
    158 |                   padding: `${MS365Spacing.m} ${MS365Spacing.l}`,
    159 |                   borderRadius: '8px 8px 0 0',
ERROR in src/components/Priority5/Priority5Dashboard.tsx:158:44
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    156 |                   color: activeTab === tab.key ? 'white' : MS365Colors.text.primary,
    157 |                   border: 'none',
  > 158 |                   padding: `${MS365Spacing.m} ${MS365Spacing.l}`,
        |                                            ^
    159 |                   borderRadius: '8px 8px 0 0',
    160 |                   cursor: 'pointer',
    161 |                   fontSize: '16px',
ERROR in src/components/Priority5/Priority5Dashboard.tsx:158:62
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    156 |                   color: activeTab === tab.key ? 'white' : MS365Colors.text.primary,
    157 |                   border: 'none',
  > 158 |                   padding: `${MS365Spacing.m} ${MS365Spacing.l}`,
        |                                                              ^
    159 |                   borderRadius: '8px 8px 0 0',
    160 |                   cursor: 'pointer',
    161 |                   fontSize: '16px',
ERROR in src/components/Priority5/Priority5Dashboard.tsx:165:37
TS2339: Property 's' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    163 |                   display: 'flex',
    164 |                   alignItems: 'center',
  > 165 |                   gap: MS365Spacing.s,
        |                                     ^
    166 |                   transition: 'all 0.3s ease',
    167 |                   whiteSpace: 'nowrap'
    168 |                 }}
ERROR in src/components/Priority5/Priority5Dashboard.tsx:177:52
TS2339: Property 'xl' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    175 |
    176 |           {/* Tab Content */}
  > 177 |           <div style={{ marginBottom: MS365Spacing.xl }}>
        |                                                    ^^
    178 |             {activeTab === 'overview' && <SystemOverview systemStats={systemStats} />}
    179 |             
    180 |             {activeTab === 'security' && (
ERROR in src/components/Priority5/Priority5Dashboard.tsx:219:31
TS2339: Property 'xl' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    217 |       {/* Overall Health Card */}
    218 |       <MS365Card style={{ 
  > 219 |         padding: MS365Spacing.xl,
        |                               ^^
    220 |         marginBottom: MS365Spacing.l,
    221 |         background: `linear-gradient(135deg, ${MS365Colors.primary}15, ${MS365Colors.info}15)`,
    222 |         border: `2px solid ${MS365Colors.primary}30`
ERROR in src/components/Priority5/Priority5Dashboard.tsx:220:36
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    218 |       <MS365Card style={{ 
    219 |         padding: MS365Spacing.xl,
  > 220 |         marginBottom: MS365Spacing.l,
        |                                    ^
    221 |         background: `linear-gradient(135deg, ${MS365Colors.primary}15, ${MS365Colors.info}15)`,
    222 |         border: `2px solid ${MS365Colors.primary}30`
    223 |       }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:221:86
TS2339: Property 'info' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    219 |         padding: MS365Spacing.xl,
    220 |         marginBottom: MS365Spacing.l,
  > 221 |         background: `linear-gradient(135deg, ${MS365Colors.primary}15, ${MS365Colors.info}15)`,
        |                                                                                      ^^^^
    222 |         border: `2px solid ${MS365Colors.primary}30`
    223 |       }}>
    224 |         <div style={{ display: 'grid', gridTemplateColumns: '1fr 2fr', gap: MS365Spacing.l, alignItems: 'center' }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:224:90
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    222 |         border: `2px solid ${MS365Colors.primary}30`
    223 |       }}>
  > 224 |         <div style={{ display: 'grid', gridTemplateColumns: '1fr 2fr', gap: MS365Spacing.l, alignItems: 'center' }}>
        |                                                                                          ^
    225 |           <div style={{ textAlign: 'center' }}>
    226 |             <div style={{ position: 'relative', width: '150px', height: '150px', margin: '0 auto' }}>
    227 |               <svg width="150" height="150" style={{ transform: 'rotate(-90deg)' }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:234:60
TS2339: Property 'success' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    232 |                   r="65" 
    233 |                   fill="none" 
  > 234 |                   stroke={overallScore >= 90 ? MS365Colors.success : overallScore >= 70 ? MS365Colors.warning : MS365Colors.error}
        |                                                            ^^^^^^^
    235 |                   strokeWidth="10"
    236 |                   strokeDasharray={`${(overallScore / 100) * 408.4} 408.4`}
    237 |                   style={{ transition: 'stroke-dasharray 2s ease' }}
ERROR in src/components/Priority5/Priority5Dashboard.tsx:234:103
TS2339: Property 'warning' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    232 |                   r="65" 
    233 |                   fill="none" 
  > 234 |                   stroke={overallScore >= 90 ? MS365Colors.success : overallScore >= 70 ? MS365Colors.warning : MS365Colors.error}
        |                                                                                                       ^^^^^^^
    235 |                   strokeWidth="10"
    236 |                   strokeDasharray={`${(overallScore / 100) * 408.4} 408.4`}
    237 |                   style={{ transition: 'stroke-dasharray 2s ease' }}
ERROR in src/components/Priority5/Priority5Dashboard.tsx:234:125
TS2339: Property 'error' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    232 |                   r="65" 
    233 |                   fill="none" 
  > 234 |                   stroke={overallScore >= 90 ? MS365Colors.success : overallScore >= 70 ? MS365Colors.warning : MS365Colors.error}
        |                                                                                                                             ^^^^^
    235 |                   strokeWidth="10"
    236 |                   strokeDasharray={`${(overallScore / 100) * 408.4} 408.4`}
    237 |                   style={{ transition: 'stroke-dasharray 2s ease' }}
ERROR in src/components/Priority5/Priority5Dashboard.tsx:247:50
TS2339: Property 'h1' does not exist on type '{ fonts: { system: string; mono: string; }; sizes: { xs: string; sm: string; base: string; lg: string; xl: string; '2xl': string; '3xl': string; '4xl': string; '5xl': string; }; weights: { light: number; normal: number; medium: number; semibold: number; bold: number; extrabold: number; }; lineHeights: { ...; }; lett...'.
    245 |                 textAlign: 'center'
    246 |               }}>
  > 247 |                 <div style={{ ...MS365Typography.h1, margin: 0, color: MS365Colors.primary }}>
        |                                                  ^^
    248 |                   {Math.round(overallScore)}
    249 |                 </div>
    250 |                 <div style={{ fontSize: '14px', color: MS365Colors.text.secondary }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:250:68
TS2339: Property 'text' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    248 |                   {Math.round(overallScore)}
    249 |                 </div>
  > 250 |                 <div style={{ fontSize: '14px', color: MS365Colors.text.secondary }}>
        |                                                                    ^^^^
    251 |                   Overall Score
    252 |                 </div>
    253 |               </div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:258:45
TS2339: Property 'h2' does not exist on type '{ fonts: { system: string; mono: string; }; sizes: { xs: string; sm: string; base: string; lg: string; xl: string; '2xl': string; '3xl': string; '4xl': string; '5xl': string; }; weights: { light: number; normal: number; medium: number; semibold: number; bold: number; extrabold: number; }; lineHeights: { ...; }; lett...'.
    256 |           
    257 |           <div>
  > 258 |             <h2 style={{ ...MS365Typography.h2, margin: 0, marginBottom: MS365Spacing.m }}>
        |                                             ^^
    259 |               System Health: {systemStats.systemHealth}
    260 |             </h2>
    261 |             
ERROR in src/components/Priority5/Priority5Dashboard.tsx:258:87
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    256 |           
    257 |           <div>
  > 258 |             <h2 style={{ ...MS365Typography.h2, margin: 0, marginBottom: MS365Spacing.m }}>
        |                                                                                       ^
    259 |               System Health: {systemStats.systemHealth}
    260 |             </h2>
    261 |             
ERROR in src/components/Priority5/Priority5Dashboard.tsx:262:94
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    260 |             </h2>
    261 |             
  > 262 |             <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: MS365Spacing.m }}>
        |                                                                                              ^
    263 |               <div style={{ 
    264 |                 padding: MS365Spacing.m,
    265 |                 backgroundColor: MS365Colors.success + '20',
ERROR in src/components/Priority5/Priority5Dashboard.tsx:264:39
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    262 |             <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: MS365Spacing.m }}>
    263 |               <div style={{ 
  > 264 |                 padding: MS365Spacing.m,
        |                                       ^
    265 |                 backgroundColor: MS365Colors.success + '20',
    266 |                 borderRadius: '8px',
    267 |                 border: `1px solid ${MS365Colors.success}30`
ERROR in src/components/Priority5/Priority5Dashboard.tsx:265:46
TS2339: Property 'success' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    263 |               <div style={{ 
    264 |                 padding: MS365Spacing.m,
  > 265 |                 backgroundColor: MS365Colors.success + '20',
        |                                              ^^^^^^^
    266 |                 borderRadius: '8px',
    267 |                 border: `1px solid ${MS365Colors.success}30`
    268 |               }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:267:50
TS2339: Property 'success' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    265 |                 backgroundColor: MS365Colors.success + '20',
    266 |                 borderRadius: '8px',
  > 267 |                 border: `1px solid ${MS365Colors.success}30`
        |                                                  ^^^^^^^
    268 |               }}>
    269 |                 <h4 style={{ margin: 0, color: MS365Colors.success }}>Security Score</h4>
    270 |                 <div style={{ fontSize: '24px', fontWeight: 'bold', color: MS365Colors.success }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:269:60
TS2339: Property 'success' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    267 |                 border: `1px solid ${MS365Colors.success}30`
    268 |               }}>
  > 269 |                 <h4 style={{ margin: 0, color: MS365Colors.success }}>Security Score</h4>
        |                                                            ^^^^^^^
    270 |                 <div style={{ fontSize: '24px', fontWeight: 'bold', color: MS365Colors.success }}>
    271 |                   {systemStats.securityScore.toFixed(0)}%
    272 |                 </div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:270:88
TS2339: Property 'success' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    268 |               }}>
    269 |                 <h4 style={{ margin: 0, color: MS365Colors.success }}>Security Score</h4>
  > 270 |                 <div style={{ fontSize: '24px', fontWeight: 'bold', color: MS365Colors.success }}>
        |                                                                                        ^^^^^^^
    271 |                   {systemStats.securityScore.toFixed(0)}%
    272 |                 </div>
    273 |               </div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:276:39
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    274 |               
    275 |               <div style={{ 
  > 276 |                 padding: MS365Spacing.m,
        |                                       ^
    277 |                 backgroundColor: MS365Colors.info + '20',
    278 |                 borderRadius: '8px',
    279 |                 border: `1px solid ${MS365Colors.info}30`
ERROR in src/components/Priority5/Priority5Dashboard.tsx:277:46
TS2339: Property 'info' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    275 |               <div style={{ 
    276 |                 padding: MS365Spacing.m,
  > 277 |                 backgroundColor: MS365Colors.info + '20',
        |                                              ^^^^
    278 |                 borderRadius: '8px',
    279 |                 border: `1px solid ${MS365Colors.info}30`
    280 |               }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:279:50
TS2339: Property 'info' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    277 |                 backgroundColor: MS365Colors.info + '20',
    278 |                 borderRadius: '8px',
  > 279 |                 border: `1px solid ${MS365Colors.info}30`
        |                                                  ^^^^
    280 |               }}>
    281 |                 <h4 style={{ margin: 0, color: MS365Colors.info }}>Performance Score</h4>
    282 |                 <div style={{ fontSize: '24px', fontWeight: 'bold', color: MS365Colors.info }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:281:60
TS2339: Property 'info' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    279 |                 border: `1px solid ${MS365Colors.info}30`
    280 |               }}>
  > 281 |                 <h4 style={{ margin: 0, color: MS365Colors.info }}>Performance Score</h4>
        |                                                            ^^^^
    282 |                 <div style={{ fontSize: '24px', fontWeight: 'bold', color: MS365Colors.info }}>
    283 |                   {systemStats.performanceScore.toFixed(0)}%
    284 |                 </div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:282:88
TS2339: Property 'info' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    280 |               }}>
    281 |                 <h4 style={{ margin: 0, color: MS365Colors.info }}>Performance Score</h4>
  > 282 |                 <div style={{ fontSize: '24px', fontWeight: 'bold', color: MS365Colors.info }}>
        |                                                                                        ^^^^
    283 |                   {systemStats.performanceScore.toFixed(0)}%
    284 |                 </div>
    285 |               </div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:295:27
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    293 |         display: 'grid', 
    294 |         gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', 
  > 295 |         gap: MS365Spacing.m,
        |                           ^
    296 |         marginBottom: MS365Spacing.l 
    297 |       }}>
    298 |         <QuickStatCard
ERROR in src/components/Priority5/Priority5Dashboard.tsx:296:36
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    294 |         gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', 
    295 |         gap: MS365Spacing.m,
  > 296 |         marginBottom: MS365Spacing.l 
        |                                    ^
    297 |       }}>
    298 |         <QuickStatCard
    299 |           title="Security Vulnerabilities"
ERROR in src/components/Priority5/Priority5Dashboard.tsx:302:66
TS2339: Property 'success' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    300 |           value={systemStats.vulnerabilities}
    301 |           icon="🛡️"
  > 302 |           color={systemStats.vulnerabilities === 0 ? MS365Colors.success : MS365Colors.error}
        |                                                                  ^^^^^^^
    303 |           status={systemStats.vulnerabilities === 0 ? "All Clear" : "Action Required"}
    304 |         />
    305 |         
ERROR in src/components/Priority5/Priority5Dashboard.tsx:302:88
TS2339: Property 'error' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    300 |           value={systemStats.vulnerabilities}
    301 |           icon="🛡️"
  > 302 |           color={systemStats.vulnerabilities === 0 ? MS365Colors.success : MS365Colors.error}
        |                                                                                        ^^^^^
    303 |           status={systemStats.vulnerabilities === 0 ? "All Clear" : "Action Required"}
    304 |         />
    305 |         
ERROR in src/components/Priority5/Priority5Dashboard.tsx:310:30
TS2339: Property 'warning' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    308 |           value={systemStats.optimizationOpportunities}
    309 |           icon="🚀"
  > 310 |           color={MS365Colors.warning}
        |                              ^^^^^^^
    311 |           status="Available"
    312 |         />
    313 |         
ERROR in src/components/Priority5/Priority5Dashboard.tsx:318:11
TS2322: Type '{ blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }' is not assignable to type 'string'.
    316 |           value={1}
    317 |           icon="👁️"
  > 318 |           color={MS365Colors.primary}
        |           ^^^^^
    319 |           status="Real-time"
    320 |         />
    321 |         
ERROR in src/components/Priority5/Priority5Dashboard.tsx:327:30
TS2339: Property 'success' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    325 |           unit="%"
    326 |           icon="⏱️"
  > 327 |           color={MS365Colors.success}
        |                              ^^^^^^^
    328 |           status="Excellent"
    329 |         />
    330 |       </div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:333:49
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    331 |
    332 |       {/* Quick Actions */}
  > 333 |       <MS365Card style={{ padding: MS365Spacing.l }}>
        |                                                 ^
    334 |         <h3 style={{ ...MS365Typography.h3, marginBottom: MS365Spacing.m }}>Quick Actions</h3>
    335 |         
    336 |         <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: MS365Spacing.m }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:334:41
TS2339: Property 'h3' does not exist on type '{ fonts: { system: string; mono: string; }; sizes: { xs: string; sm: string; base: string; lg: string; xl: string; '2xl': string; '3xl': string; '4xl': string; '5xl': string; }; weights: { light: number; normal: number; medium: number; semibold: number; bold: number; extrabold: number; }; lineHeights: { ...; }; lett...'.
    332 |       {/* Quick Actions */}
    333 |       <MS365Card style={{ padding: MS365Spacing.l }}>
  > 334 |         <h3 style={{ ...MS365Typography.h3, marginBottom: MS365Spacing.m }}>Quick Actions</h3>
        |                                         ^^
    335 |         
    336 |         <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: MS365Spacing.m }}>
    337 |           <MS365Button style={{ padding: MS365Spacing.m }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:334:72
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    332 |       {/* Quick Actions */}
    333 |       <MS365Card style={{ padding: MS365Spacing.l }}>
  > 334 |         <h3 style={{ ...MS365Typography.h3, marginBottom: MS365Spacing.m }}>Quick Actions</h3>
        |                                                                        ^
    335 |         
    336 |         <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: MS365Spacing.m }}>
    337 |           <MS365Button style={{ padding: MS365Spacing.m }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:336:119
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    334 |         <h3 style={{ ...MS365Typography.h3, marginBottom: MS365Spacing.m }}>Quick Actions</h3>
    335 |         
  > 336 |         <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: MS365Spacing.m }}>
        |                                                                                                                       ^
    337 |           <MS365Button style={{ padding: MS365Spacing.m }}>
    338 |             🔍 Run Security Scan
    339 |           </MS365Button>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:337:55
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    335 |         
    336 |         <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: MS365Spacing.m }}>
  > 337 |           <MS365Button style={{ padding: MS365Spacing.m }}>
        |                                                       ^
    338 |             🔍 Run Security Scan
    339 |           </MS365Button>
    340 |           <MS365Button style={{ padding: MS365Spacing.m }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:340:55
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    338 |             🔍 Run Security Scan
    339 |           </MS365Button>
  > 340 |           <MS365Button style={{ padding: MS365Spacing.m }}>
        |                                                       ^
    341 |             ⚡ Performance Analysis
    342 |           </MS365Button>
    343 |           <MS365Button style={{ padding: MS365Spacing.m }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:343:55
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    341 |             ⚡ Performance Analysis
    342 |           </MS365Button>
  > 343 |           <MS365Button style={{ padding: MS365Spacing.m }}>
        |                                                       ^
    344 |             🧹 Clear Cache
    345 |           </MS365Button>
    346 |           <MS365Button style={{ padding: MS365Spacing.m }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:346:55
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    344 |             🧹 Clear Cache
    345 |           </MS365Button>
  > 346 |           <MS365Button style={{ padding: MS365Spacing.m }}>
        |                                                       ^
    347 |             📊 Generate Report
    348 |           </MS365Button>
    349 |         </div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:367:27
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    365 | const QuickStatCard: React.FC<QuickStatCardProps> = ({ title, value, unit = '', icon, color, status }) => (
    366 |   <MS365Card style={{ 
  > 367 |     padding: MS365Spacing.l,
        |                           ^
    368 |     background: `linear-gradient(135deg, ${color}15, ${color}05)`,
    369 |     border: `1px solid ${color}30`
    370 |   }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:373:68
TS2339: Property 's' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    371 |     <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
    372 |       <div>
  > 373 |         <div style={{ fontSize: '32px', marginBottom: MS365Spacing.s }}>{icon}</div>
        |                                                                    ^
    374 |         <h4 style={{ ...MS365Typography.h4, margin: 0, color: MS365Colors.text.secondary }}>
    375 |           {title}
    376 |         </h4>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:374:41
TS2339: Property 'h4' does not exist on type '{ fonts: { system: string; mono: string; }; sizes: { xs: string; sm: string; base: string; lg: string; xl: string; '2xl': string; '3xl': string; '4xl': string; '5xl': string; }; weights: { light: number; normal: number; medium: number; semibold: number; bold: number; extrabold: number; }; lineHeights: { ...; }; lett...'.
    372 |       <div>
    373 |         <div style={{ fontSize: '32px', marginBottom: MS365Spacing.s }}>{icon}</div>
  > 374 |         <h4 style={{ ...MS365Typography.h4, margin: 0, color: MS365Colors.text.secondary }}>
        |                                         ^^
    375 |           {title}
    376 |         </h4>
    377 |         <div style={{ display: 'flex', alignItems: 'baseline', gap: '4px', marginTop: MS365Spacing.s }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:374:75
TS2339: Property 'text' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    372 |       <div>
    373 |         <div style={{ fontSize: '32px', marginBottom: MS365Spacing.s }}>{icon}</div>
  > 374 |         <h4 style={{ ...MS365Typography.h4, margin: 0, color: MS365Colors.text.secondary }}>
        |                                                                           ^^^^
    375 |           {title}
    376 |         </h4>
    377 |         <div style={{ display: 'flex', alignItems: 'baseline', gap: '4px', marginTop: MS365Spacing.s }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:377:100
TS2339: Property 's' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    375 |           {title}
    376 |         </h4>
  > 377 |         <div style={{ display: 'flex', alignItems: 'baseline', gap: '4px', marginTop: MS365Spacing.s }}>
        |                                                                                                    ^
    378 |           <span style={{ ...MS365Typography.h2, color, margin: 0 }}>
    379 |             {value}
    380 |           </span>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:378:45
TS2339: Property 'h2' does not exist on type '{ fonts: { system: string; mono: string; }; sizes: { xs: string; sm: string; base: string; lg: string; xl: string; '2xl': string; '3xl': string; '4xl': string; '5xl': string; }; weights: { light: number; normal: number; medium: number; semibold: number; bold: number; extrabold: number; }; lineHeights: { ...; }; lett...'.
    376 |         </h4>
    377 |         <div style={{ display: 'flex', alignItems: 'baseline', gap: '4px', marginTop: MS365Spacing.s }}>
  > 378 |           <span style={{ ...MS365Typography.h2, color, margin: 0 }}>
        |                                             ^^
    379 |             {value}
    380 |           </span>
    381 |           <span style={{ ...MS365Typography.body, color: MS365Colors.text.secondary }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:381:45
TS2339: Property 'body' does not exist on type '{ fonts: { system: string; mono: string; }; sizes: { xs: string; sm: string; base: string; lg: string; xl: string; '2xl': string; '3xl': string; '4xl': string; '5xl': string; }; weights: { light: number; normal: number; medium: number; semibold: number; bold: number; extrabold: number; }; lineHeights: { ...; }; lett...'.
    379 |             {value}
    380 |           </span>
  > 381 |           <span style={{ ...MS365Typography.body, color: MS365Colors.text.secondary }}>
        |                                             ^^^^
    382 |             {unit}
    383 |           </span>
    384 |         </div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:381:70
TS2339: Property 'text' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    379 |             {value}
    380 |           </span>
  > 381 |           <span style={{ ...MS365Typography.body, color: MS365Colors.text.secondary }}>
        |                                                                      ^^^^
    382 |             {unit}
    383 |           </span>
    384 |         </div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:442:88
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    440 |   return (
    441 |     <div>
  > 442 |       <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: MS365Spacing.l }}>
        |                                                                                        ^
    443 |         {/* Optimization Opportunities */}
    444 |         <MS365Card style={{ padding: MS365Spacing.l }}>
    445 |           <h3 style={{ ...MS365Typography.h3, marginBottom: MS365Spacing.m }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:444:51
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    442 |       <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: MS365Spacing.l }}>
    443 |         {/* Optimization Opportunities */}
  > 444 |         <MS365Card style={{ padding: MS365Spacing.l }}>
        |                                                   ^
    445 |           <h3 style={{ ...MS365Typography.h3, marginBottom: MS365Spacing.m }}>
    446 |             Optimization Opportunities
    447 |           </h3>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:445:43
TS2339: Property 'h3' does not exist on type '{ fonts: { system: string; mono: string; }; sizes: { xs: string; sm: string; base: string; lg: string; xl: string; '2xl': string; '3xl': string; '4xl': string; '5xl': string; }; weights: { light: number; normal: number; medium: number; semibold: number; bold: number; extrabold: number; }; lineHeights: { ...; }; lett...'.
    443 |         {/* Optimization Opportunities */}
    444 |         <MS365Card style={{ padding: MS365Spacing.l }}>
  > 445 |           <h3 style={{ ...MS365Typography.h3, marginBottom: MS365Spacing.m }}>
        |                                           ^^
    446 |             Optimization Opportunities
    447 |           </h3>
    448 |           
ERROR in src/components/Priority5/Priority5Dashboard.tsx:445:74
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    443 |         {/* Optimization Opportunities */}
    444 |         <MS365Card style={{ padding: MS365Spacing.l }}>
  > 445 |           <h3 style={{ ...MS365Typography.h3, marginBottom: MS365Spacing.m }}>
        |                                                                          ^
    446 |             Optimization Opportunities
    447 |           </h3>
    448 |           
ERROR in src/components/Priority5/Priority5Dashboard.tsx:449:52
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    447 |           </h3>
    448 |           
  > 449 |           <div style={{ marginBottom: MS365Spacing.l }}>
        |                                                    ^
    450 |             {[
    451 |               { text: "Enable gzip compression", impact: "High", savings: "60% size reduction" },
    452 |               { text: "Implement lazy loading for images", impact: "Medium", savings: "2s faster load" },
ERROR in src/components/Priority5/Priority5Dashboard.tsx:457:39
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    455 |             ].map((opportunity, index) => (
    456 |               <div key={index} style={{
  > 457 |                 padding: MS365Spacing.m,
        |                                       ^
    458 |                 backgroundColor: MS365Colors.warning + '10',
    459 |                 border: `1px solid ${MS365Colors.warning}30`,
    460 |                 borderRadius: '8px',
ERROR in src/components/Priority5/Priority5Dashboard.tsx:458:46
TS2339: Property 'warning' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    456 |               <div key={index} style={{
    457 |                 padding: MS365Spacing.m,
  > 458 |                 backgroundColor: MS365Colors.warning + '10',
        |                                              ^^^^^^^
    459 |                 border: `1px solid ${MS365Colors.warning}30`,
    460 |                 borderRadius: '8px',
    461 |                 marginBottom: MS365Spacing.s
ERROR in src/components/Priority5/Priority5Dashboard.tsx:459:50
TS2339: Property 'warning' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    457 |                 padding: MS365Spacing.m,
    458 |                 backgroundColor: MS365Colors.warning + '10',
  > 459 |                 border: `1px solid ${MS365Colors.warning}30`,
        |                                                  ^^^^^^^
    460 |                 borderRadius: '8px',
    461 |                 marginBottom: MS365Spacing.s
    462 |               }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:461:44
TS2339: Property 's' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    459 |                 border: `1px solid ${MS365Colors.warning}30`,
    460 |                 borderRadius: '8px',
  > 461 |                 marginBottom: MS365Spacing.s
        |                                            ^
    462 |               }}>
    463 |                 <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
    464 |                   <div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:468:72
TS2339: Property 'text' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    466 |                       {opportunity.text}
    467 |                     </div>
  > 468 |                     <div style={{ fontSize: '12px', color: MS365Colors.text.secondary }}>
        |                                                                        ^^^^
    469 |                       Impact: {opportunity.impact} • {opportunity.savings}
    470 |                     </div>
    471 |                   </div>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:475:82
TS2339: Property 'error' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    473 |                     padding: '2px 6px',
    474 |                     borderRadius: '8px',
  > 475 |                     backgroundColor: opportunity.impact === 'High' ? MS365Colors.error + '20' : MS365Colors.warning + '20',
        |                                                                                  ^^^^^
    476 |                     color: opportunity.impact === 'High' ? MS365Colors.error : MS365Colors.warning,
    477 |                     fontSize: '10px',
    478 |                     fontWeight: 'bold'
ERROR in src/components/Priority5/Priority5Dashboard.tsx:475:109
TS2339: Property 'warning' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    473 |                     padding: '2px 6px',
    474 |                     borderRadius: '8px',
  > 475 |                     backgroundColor: opportunity.impact === 'High' ? MS365Colors.error + '20' : MS365Colors.warning + '20',
        |                                                                                                             ^^^^^^^
    476 |                     color: opportunity.impact === 'High' ? MS365Colors.error : MS365Colors.warning,
    477 |                     fontSize: '10px',
    478 |                     fontWeight: 'bold'
ERROR in src/components/Priority5/Priority5Dashboard.tsx:476:72
TS2339: Property 'error' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    474 |                     borderRadius: '8px',
    475 |                     backgroundColor: opportunity.impact === 'High' ? MS365Colors.error + '20' : MS365Colors.warning + '20',
  > 476 |                     color: opportunity.impact === 'High' ? MS365Colors.error : MS365Colors.warning,
        |                                                                        ^^^^^
    477 |                     fontSize: '10px',
    478 |                     fontWeight: 'bold'
    479 |                   }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:476:92
TS2339: Property 'warning' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    474 |                     borderRadius: '8px',
    475 |                     backgroundColor: opportunity.impact === 'High' ? MS365Colors.error + '20' : MS365Colors.warning + '20',
  > 476 |                     color: opportunity.impact === 'High' ? MS365Colors.error : MS365Colors.warning,
        |                                                                                            ^^^^^^^
    477 |                     fontSize: '10px',
    478 |                     fontWeight: 'bold'
    479 |                   }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:492:44
TS2339: Property 'success' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    490 |             style={{ 
    491 |               width: '100%',
  > 492 |               backgroundColor: MS365Colors.success,
        |                                            ^^^^^^^
    493 |               borderColor: MS365Colors.success 
    494 |             }}
    495 |           >
ERROR in src/components/Priority5/Priority5Dashboard.tsx:493:40
TS2339: Property 'success' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    491 |               width: '100%',
    492 |               backgroundColor: MS365Colors.success,
  > 493 |               borderColor: MS365Colors.success 
        |                                        ^^^^^^^
    494 |             }}
    495 |           >
    496 |             {isOptimizing ? 'Optimizing...' : 'Run All Optimizations'}
ERROR in src/components/Priority5/Priority5Dashboard.tsx:501:51
TS2339: Property 'l' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    499 |
    500 |         {/* Optimization Results */}
  > 501 |         <MS365Card style={{ padding: MS365Spacing.l }}>
        |                                                   ^
    502 |           <h3 style={{ ...MS365Typography.h3, marginBottom: MS365Spacing.m }}>
    503 |             Optimization Progress
    504 |           </h3>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:502:43
TS2339: Property 'h3' does not exist on type '{ fonts: { system: string; mono: string; }; sizes: { xs: string; sm: string; base: string; lg: string; xl: string; '2xl': string; '3xl': string; '4xl': string; '5xl': string; }; weights: { light: number; normal: number; medium: number; semibold: number; bold: number; extrabold: number; }; lineHeights: { ...; }; lett...'.
    500 |         {/* Optimization Results */}
    501 |         <MS365Card style={{ padding: MS365Spacing.l }}>
  > 502 |           <h3 style={{ ...MS365Typography.h3, marginBottom: MS365Spacing.m }}>
        |                                           ^^
    503 |             Optimization Progress
    504 |           </h3>
    505 |           
ERROR in src/components/Priority5/Priority5Dashboard.tsx:502:74
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    500 |         {/* Optimization Results */}
    501 |         <MS365Card style={{ padding: MS365Spacing.l }}>
  > 502 |           <h3 style={{ ...MS365Typography.h3, marginBottom: MS365Spacing.m }}>
        |                                                                          ^
    503 |             Optimization Progress
    504 |           </h3>
    505 |           
ERROR in src/components/Priority5/Priority5Dashboard.tsx:510:35
TS2339: Property 's' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    508 |             maxHeight: '400px',
    509 |             overflowY: 'auto',
  > 510 |             padding: MS365Spacing.s,
        |                                   ^
    511 |             backgroundColor: MS365Colors.background.secondary,
    512 |             borderRadius: '8px',
    513 |             fontFamily: 'Consolas, Monaco, monospace',
ERROR in src/components/Priority5/Priority5Dashboard.tsx:517:48
TS2339: Property 'text' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    515 |           }}>
    516 |             {optimizationResults.length === 0 && !isOptimizing && (
  > 517 |               <div style={{ color: MS365Colors.text.secondary, textAlign: 'center', paddingTop: '50px' }}>
        |                                                ^^^^
    518 |                 Click "Run All Optimizations" to start the process
    519 |               </div>
    520 |             )}
ERROR in src/components/Priority5/Priority5Dashboard.tsx:525:36
TS2339: Property 'success' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    523 |               <div key={index} style={{ 
    524 |                 marginBottom: '8px',
  > 525 |                 color: MS365Colors.success,
        |                                    ^^^^^^^
    526 |                 display: 'flex',
    527 |                 alignItems: 'center',
    528 |                 gap: '8px'
ERROR in src/components/Priority5/Priority5Dashboard.tsx:530:51
TS2339: Property 'success' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    528 |                 gap: '8px'
    529 |               }}>
  > 530 |                 <span style={{ color: MS365Colors.success }}>✓</span>
        |                                                   ^^^^^^^
    531 |                 {result}
    532 |               </div>
    533 |             ))}
ERROR in src/components/Priority5/Priority5Dashboard.tsx:540:17
TS2322: Type '{ blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }' is not assignable to type 'Color | undefined'.
    538 |                 alignItems: 'center',
    539 |                 gap: '8px',
  > 540 |                 color: MS365Colors.primary 
        |                 ^^^^^
    541 |               }}>
    542 |                 <MS365Spinner size="small" />
    543 |                 Processing...
ERROR in src/components/Priority5/Priority5Dashboard.tsx:564:27
TS2339: Property 'xl' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    562 |     justifyContent: 'center',
    563 |     alignItems: 'center',
  > 564 |     padding: MS365Spacing.xl,
        |                           ^^
    565 |     minHeight: '300px'
    566 |   }}>
    567 |     <MS365Spinner size="large" />
ERROR in src/components/Priority5/Priority5Dashboard.tsx:569:31
TS2339: Property 'm' does not exist on type '{ 0: string; 1: string; 2: string; 3: string; 4: string; 5: string; 6: string; 7: string; 8: string; 10: string; 12: string; 16: string; 20: string; 24: string; 32: string; 40: string; 48: string; 56: string; 64: string; }'.
    567 |     <MS365Spinner size="large" />
    568 |     <div style={{ 
  > 569 |       marginTop: MS365Spacing.m,
        |                               ^
    570 |       color: MS365Colors.text.secondary,
    571 |       fontSize: '16px'
    572 |     }}>
ERROR in src/components/Priority5/Priority5Dashboard.tsx:570:26
TS2339: Property 'text' does not exist on type '{ primary: { blue: { 50: string; 100: string; 200: string; 300: string; 400: string; 500: string; 600: string; 700: string; 800: string; 900: string; }; green: { 50: string; 100: string; 200: string; 300: string; 400: string; ... 4 more ...; 900: string; }; red: { ...; }; purple: { ...; }; orange: { ...; }; }; neutr...'.
    568 |     <div style={{ 
    569 |       marginTop: MS365Spacing.m,
  > 570 |       color: MS365Colors.text.secondary,
        |                          ^^^^
    571 |       fontSize: '16px'
    572 |     }}>
    573 |       {message}
ERROR in src/components/Reports/AdvancedReportingDashboard.tsx:95:64
TS2307: Cannot find module '../../utils/dataExport' or its corresponding type declarations.
    93 |   ScatterChart,
    94 | } from 'recharts';
  > 95 | import { exportAnalyticsData, ExportData, ExportOptions } from '../../utils/dataExport';
       |                                                                ^^^^^^^^^^^^^^^^^^^^^^^^
    96 |
    97 | // ====================================
    98 | // 🎯 TYPES & INTERFACES
ERROR in src/components/Reports/AdvancedReportingDashboard.tsx:167:7
TS2739: Type '{ metrics: string[]; groupBy: string; }' is missing the following properties from type '{ dateRange: { start: Date; end: Date; }; marketplaces: string[]; categories: string[]; metrics: string[]; groupBy: string; }': dateRange, marketplaces, categories
    165 |     isBuiltIn: true,
    166 |     config: {
  > 167 |       filters: {
        |       ^^^^^^^
    168 |         metrics: ['revenue', 'orders', 'averageOrderValue'],
    169 |         groupBy: 'date',
    170 |       },
ERROR in src/components/Reports/AdvancedReportingDashboard.tsx:188:7
TS2739: Type '{ metrics: string[]; groupBy: string; }' is missing the following properties from type '{ dateRange: { start: Date; end: Date; }; marketplaces: string[]; categories: string[]; metrics: string[]; groupBy: string; }': dateRange, marketplaces, categories
    186 |     isBuiltIn: true,
    187 |     config: {
  > 188 |       filters: {
        |       ^^^^^^^
    189 |         metrics: ['revenue', 'orders', 'growth'],
    190 |         groupBy: 'marketplace',
    191 |       },
ERROR in src/components/Reports/AdvancedReportingDashboard.tsx:209:7
TS2739: Type '{ metrics: string[]; groupBy: string; }' is missing the following properties from type '{ dateRange: { start: Date; end: Date; }; marketplaces: string[]; categories: string[]; metrics: string[]; groupBy: string; }': dateRange, marketplaces, categories
    207 |     isBuiltIn: true,
    208 |     config: {
  > 209 |       filters: {
        |       ^^^^^^^
    210 |         metrics: ['customerValue', 'retention', 'acquisition'],
    211 |         groupBy: 'segment',
    212 |       },
ERROR in src/components/Reports/AdvancedReportingDashboard.tsx:230:7
TS2739: Type '{ metrics: string[]; groupBy: string; }' is missing the following properties from type '{ dateRange: { start: Date; end: Date; }; marketplaces: string[]; categories: string[]; metrics: string[]; groupBy: string; }': dateRange, marketplaces, categories
    228 |     isBuiltIn: true,
    229 |     config: {
  > 230 |       filters: {
        |       ^^^^^^^
    231 |         metrics: ['revenue', 'profit', 'margin', 'costs'],
    232 |         groupBy: 'month',
    233 |       },
ERROR in src/components/Reports/AdvancedReportingDashboard.tsx:251:7
TS2739: Type '{ metrics: string[]; groupBy: string; }' is missing the following properties from type '{ dateRange: { start: Date; end: Date; }; marketplaces: string[]; categories: string[]; metrics: string[]; groupBy: string; }': dateRange, marketplaces, categories
    249 |     isBuiltIn: true,
    250 |     config: {
  > 251 |       filters: {
        |       ^^^^^^^
    252 |         metrics: ['orderProcessingTime', 'stockTurnover', 'deliveryTime'],
    253 |         groupBy: 'week',
    254 |       },
ERROR in src/components/Search/AdvancedSearch.tsx:22:3
TS2305: Module '"@mui/material"' has no exported member 'DatePicker'.
    20 |   Select,
    21 |   Slider,
  > 22 |   DatePicker,
       |   ^^^^^^^^^^
    23 |   Checkbox,
    24 |   FormControlLabel,
    25 |   IconButton,
ERROR in src/components/Security/SecurityDashboard.tsx:44:8
TS2307: Cannot find module '@/components/ui' or its corresponding type declarations.
    42 |   TooltipProvider,
    43 |   TooltipTrigger
  > 44 | } from '@/components/ui';
       |        ^^^^^^^^^^^^^^^^^
    45 | import {
    46 |   ShieldCheck,
    47 |   ShieldAlert,
ERROR in src/components/Security/SecurityDashboard.tsx:76:8
TS2307: Cannot find module 'lucide-react' or its corresponding type declarations.
    74 |   Network,
    75 |   Server
  > 76 | } from 'lucide-react';
       |        ^^^^^^^^^^^^^^
    77 | import { 
    78 |   LineChart, 
    79 |   Line, 
ERROR in src/components/Security/SecurityDashboard.tsx:94:65
TS2307: Cannot find module '../../services/security/VulnerabilityScanner' or its corresponding type declarations.
    92 |   Bar
    93 | } from 'recharts';
  > 94 | import VulnerabilityScanner, { Vulnerability, ScanResult } from '../../services/security/VulnerabilityScanner';
       |                                                                 ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    95 | import SecurityManager from '../../services/security/SecurityManager';
    96 |
    97 | // Types
ERROR in src/components/Security/SecurityDashboard.tsx:95:29
TS2307: Cannot find module '../../services/security/SecurityManager' or its corresponding type declarations.
    93 | } from 'recharts';
    94 | import VulnerabilityScanner, { Vulnerability, ScanResult } from '../../services/security/VulnerabilityScanner';
  > 95 | import SecurityManager from '../../services/security/SecurityManager';
       |                             ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    96 |
    97 | // Types
    98 | interface SecurityMetrics {
ERROR in src/components/Security/SecurityDashboard.tsx:600:66
TS7006: Parameter 'value' implicitly has an 'any' type.
    598 |                 </div>
    599 |                 
  > 600 |                 <Select value={filters.severity} onValueChange={(value) => setFilters(prev => ({ ...prev, severity: value }))}>
        |                                                                  ^^^^^
    601 |                   <SelectTrigger className="w-32">
    602 |                     <SelectValue placeholder="Ciddiyet" />
    603 |                   </SelectTrigger>
ERROR in src/components/Security/SecurityDashboard.tsx:613:64
TS7006: Parameter 'value' implicitly has an 'any' type.
    611 |                 </Select>
    612 |
  > 613 |                 <Select value={filters.status} onValueChange={(value) => setFilters(prev => ({ ...prev, status: value }))}>
        |                                                                ^^^^^
    614 |                   <SelectTrigger className="w-32">
    615 |                     <SelectValue placeholder="Durum" />
    616 |                   </SelectTrigger>
ERROR in src/components/Security/SecurityDashboard.tsx:879:20
TS2304: Cannot find name 'FileText'.
    877 |                 </Button>
    878 |                 <Button variant="outline" className="h-24 flex-col">
  > 879 |                   <FileText className="w-6 h-6 mb-2" />
        |                    ^^^^^^^^
    880 |                   Uyumluluk Raporu
    881 |                 </Button>
    882 |                 <Button variant="outline" className="h-24 flex-col">
ERROR in src/components/Security/SecurityDashboard.tsx:912:27
TS7006: Parameter 'value' implicitly has an 'any' type.
    910 |         <Select 
    911 |           value={vulnerability.status} 
  > 912 |           onValueChange={(value) => onStatusUpdate(vulnerability.id, value as Vulnerability['status'])}
        |                           ^^^^^
    913 |         >
    914 |           <SelectTrigger className="w-40">
    915 |             <SelectValue />
ERROR in src/components/Security/SecurityDashboard.tsx:936:40
TS7006: Parameter 'component' implicitly has an 'any' type.
    934 |         <h4 className="font-semibold mb-2">Etkilenen Bileşenler</h4>
    935 |         <div className="space-y-2">
  > 936 |           {vulnerability.affected.map((component, index) => (
        |                                        ^^^^^^^^^
    937 |             <div key={index} className="flex items-center gap-3 p-2 bg-gray-50 rounded">
    938 |               <Badge variant="outline">{component.type}</Badge>
    939 |               <span className="font-medium">{component.name}</span>
ERROR in src/components/Security/SecurityDashboard.tsx:936:51
TS7006: Parameter 'index' implicitly has an 'any' type.
    934 |         <h4 className="font-semibold mb-2">Etkilenen Bileşenler</h4>
    935 |         <div className="space-y-2">
  > 936 |           {vulnerability.affected.map((component, index) => (
        |                                                   ^^^^^
    937 |             <div key={index} className="flex items-center gap-3 p-2 bg-gray-50 rounded">
    938 |               <Badge variant="outline">{component.type}</Badge>
    939 |               <span className="font-medium">{component.name}</span>
ERROR in src/components/Security/SecurityDashboard.tsx:951:43
TS7006: Parameter 'action' implicitly has an 'any' type.
    949 |         <h4 className="font-semibold mb-2">Çözüm Adımları</h4>
    950 |         <div className="space-y-3">
  > 951 |           {vulnerability.remediation.map((action, index) => (
        |                                           ^^^^^^
    952 |             <div key={index} className="border rounded-lg p-4">
    953 |               <div className="flex items-center justify-between mb-2">
    954 |                 <Badge variant="outline">{action.type}</Badge>
ERROR in src/components/Security/SecurityDashboard.tsx:951:51
TS7006: Parameter 'index' implicitly has an 'any' type.
    949 |         <h4 className="font-semibold mb-2">Çözüm Adımları</h4>
    950 |         <div className="space-y-3">
  > 951 |           {vulnerability.remediation.map((action, index) => (
        |                                                   ^^^^^
    952 |             <div key={index} className="border rounded-lg p-4">
    953 |               <div className="flex items-center justify-between mb-2">
    954 |                 <Badge variant="outline">{action.type}</Badge>
ERROR in src/components/Security/SecurityDashboard.tsx:968:38
TS7006: Parameter 'step' implicitly has an 'any' type.
    966 |                 <strong className="text-sm">Adımlar:</strong>
    967 |                 <ol className="list-decimal list-inside mt-1 text-sm text-gray-600">
  > 968 |                   {action.steps.map((step, stepIndex) => (
        |                                      ^^^^
    969 |                     <li key={stepIndex}>{step}</li>
    970 |                   ))}
    971 |                 </ol>
ERROR in src/components/Security/SecurityDashboard.tsx:968:44
TS7006: Parameter 'stepIndex' implicitly has an 'any' type.
    966 |                 <strong className="text-sm">Adımlar:</strong>
    967 |                 <ol className="list-decimal list-inside mt-1 text-sm text-gray-600">
  > 968 |                   {action.steps.map((step, stepIndex) => (
        |                                            ^^^^^^^^^
    969 |                     <li key={stepIndex}>{step}</li>
    970 |                   ))}
    971 |                 </ol>
ERROR in src/components/Security/SecurityDashboard.tsx:983:42
TS7006: Parameter 'evidence' implicitly has an 'any' type.
    981 |           <h4 className="font-semibold mb-2">Kanıtlar</h4>
    982 |           <div className="space-y-2">
  > 983 |             {vulnerability.evidence.map((evidence, index) => (
        |                                          ^^^^^^^^
    984 |               <div key={index} className="border rounded p-3">
    985 |                 <div className="flex items-center gap-2 mb-2">
    986 |                   <Badge variant="outline">{evidence.type}</Badge>
ERROR in src/components/Security/SecurityDashboard.tsx:983:52
TS7006: Parameter 'index' implicitly has an 'any' type.
    981 |           <h4 className="font-semibold mb-2">Kanıtlar</h4>
    982 |           <div className="space-y-2">
  > 983 |             {vulnerability.evidence.map((evidence, index) => (
        |                                                    ^^^^^
    984 |               <div key={index} className="border rounded p-3">
    985 |                 <div className="flex items-center gap-2 mb-2">
    986 |                   <Badge variant="outline">{evidence.type}</Badge>
ERROR in src/demo/Microsoft365Demo.tsx:9:21
TS2614: Module '"../components/Microsoft365/MS365Card"' has no exported member 'MS365SuccessCard'. Did you mean to use 'import MS365SuccessCard from "../components/Microsoft365/MS365Card"' instead?
     7 | import React, { useState } from 'react';
     8 | import { Microsoft365DesignSystem } from '../theme/microsoft365-design-system';
  >  9 | import MS365Card, { MS365SuccessCard, MS365WarningCard, MS365ErrorCard, MS365InfoCard, MS365MarketplaceCard } from '../components/Microsoft365/MS365Card';
       |                     ^^^^^^^^^^^^^^^^
    10 | import MS365Button, { 
    11 |   MS365PrimaryButton, 
    12 |   MS365SecondaryButton, 
ERROR in src/demo/Microsoft365Demo.tsx:9:39
TS2614: Module '"../components/Microsoft365/MS365Card"' has no exported member 'MS365WarningCard'. Did you mean to use 'import MS365WarningCard from "../components/Microsoft365/MS365Card"' instead?
     7 | import React, { useState } from 'react';
     8 | import { Microsoft365DesignSystem } from '../theme/microsoft365-design-system';
  >  9 | import MS365Card, { MS365SuccessCard, MS365WarningCard, MS365ErrorCard, MS365InfoCard, MS365MarketplaceCard } from '../components/Microsoft365/MS365Card';
       |                                       ^^^^^^^^^^^^^^^^
    10 | import MS365Button, { 
    11 |   MS365PrimaryButton, 
    12 |   MS365SecondaryButton, 
ERROR in src/demo/Microsoft365Demo.tsx:9:57
TS2614: Module '"../components/Microsoft365/MS365Card"' has no exported member 'MS365ErrorCard'. Did you mean to use 'import MS365ErrorCard from "../components/Microsoft365/MS365Card"' instead?
     7 | import React, { useState } from 'react';
     8 | import { Microsoft365DesignSystem } from '../theme/microsoft365-design-system';
  >  9 | import MS365Card, { MS365SuccessCard, MS365WarningCard, MS365ErrorCard, MS365InfoCard, MS365MarketplaceCard } from '../components/Microsoft365/MS365Card';
       |                                                         ^^^^^^^^^^^^^^
    10 | import MS365Button, { 
    11 |   MS365PrimaryButton, 
    12 |   MS365SecondaryButton, 
ERROR in src/demo/Microsoft365Demo.tsx:9:73
TS2724: '"../components/Microsoft365/MS365Card"' has no exported member named 'MS365InfoCard'. Did you mean 'MS365Card'?
     7 | import React, { useState } from 'react';
     8 | import { Microsoft365DesignSystem } from '../theme/microsoft365-design-system';
  >  9 | import MS365Card, { MS365SuccessCard, MS365WarningCard, MS365ErrorCard, MS365InfoCard, MS365MarketplaceCard } from '../components/Microsoft365/MS365Card';
       |                                                                         ^^^^^^^^^^^^^
    10 | import MS365Button, { 
    11 |   MS365PrimaryButton, 
    12 |   MS365SecondaryButton, 
ERROR in src/demo/Microsoft365Demo.tsx:9:88
TS2614: Module '"../components/Microsoft365/MS365Card"' has no exported member 'MS365MarketplaceCard'. Did you mean to use 'import MS365MarketplaceCard from "../components/Microsoft365/MS365Card"' instead?
     7 | import React, { useState } from 'react';
     8 | import { Microsoft365DesignSystem } from '../theme/microsoft365-design-system';
  >  9 | import MS365Card, { MS365SuccessCard, MS365WarningCard, MS365ErrorCard, MS365InfoCard, MS365MarketplaceCard } from '../components/Microsoft365/MS365Card';
       |                                                                                        ^^^^^^^^^^^^^^^^^^^^
    10 | import MS365Button, { 
    11 |   MS365PrimaryButton, 
    12 |   MS365SecondaryButton, 
ERROR in src/demo/Microsoft365Demo.tsx:13:3
TS2614: Module '"../components/Microsoft365/MS365Button"' has no exported member 'MS365SuccessButton'. Did you mean to use 'import MS365SuccessButton from "../components/Microsoft365/MS365Button"' instead?
    11 |   MS365PrimaryButton, 
    12 |   MS365SecondaryButton, 
  > 13 |   MS365SuccessButton, 
       |   ^^^^^^^^^^^^^^^^^^
    14 |   MS365WarningButton, 
    15 |   MS365ErrorButton,
    16 |   MS365GhostButton,
ERROR in src/demo/Microsoft365Demo.tsx:14:3
TS2724: '"../components/Microsoft365/MS365Button"' has no exported member named 'MS365WarningButton'. Did you mean 'MS365LinkButton'?
    12 |   MS365SecondaryButton, 
    13 |   MS365SuccessButton, 
  > 14 |   MS365WarningButton, 
       |   ^^^^^^^^^^^^^^^^^^
    15 |   MS365ErrorButton,
    16 |   MS365GhostButton,
    17 |   MS365LinkButton 
ERROR in src/demo/Microsoft365Demo.tsx:15:3
TS2724: '"../components/Microsoft365/MS365Button"' has no exported member named 'MS365ErrorButton'. Did you mean 'MS365Button'?
    13 |   MS365SuccessButton, 
    14 |   MS365WarningButton, 
  > 15 |   MS365ErrorButton,
       |   ^^^^^^^^^^^^^^^^
    16 |   MS365GhostButton,
    17 |   MS365LinkButton 
    18 | } from '../components/Microsoft365/MS365Button';
ERROR in src/demo/Microsoft365Demo.tsx:173:48
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    171 |       <div style={sectionStyle}>
    172 |         <h2 style={sectionTitleStyle}>🔤 Typography Scale - Academic "Small, Clean" Requirement</h2>
  > 173 |         <MS365Card title="Typography Examples" size="lg">
        |                                                ^^^^
    174 |           <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[3] }}>
    175 |             <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.xs, opacity: Microsoft365DesignSystem.typography.textOpacity.primary }}>
    176 |               XS (12px) - Very small, clean text
ERROR in src/demo/Microsoft365Demo.tsx:320:41
TS2322: Type '"sm"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    318 |         </h3>
    319 |         <div style={{ display: 'flex', flexDirection: 'column', gap: Microsoft365DesignSystem.spacing[4] }}>
  > 320 |           <MS365Card title="Small Card" size="sm">
        |                                         ^^^^
    321 |             Compact design for tight spaces.
    322 |           </MS365Card>
    323 |           <MS365Card title="Medium Card" size="md">
ERROR in src/demo/Microsoft365Demo.tsx:323:42
TS2322: Type '"md"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    321 |             Compact design for tight spaces.
    322 |           </MS365Card>
  > 323 |           <MS365Card title="Medium Card" size="md">
        |                                          ^^^^
    324 |             Default size, perfect for most use cases.
    325 |           </MS365Card>
    326 |           <MS365Card title="Large Card" size="lg">
ERROR in src/demo/Microsoft365Demo.tsx:326:41
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    324 |             Default size, perfect for most use cases.
    325 |           </MS365Card>
  > 326 |           <MS365Card title="Large Card" size="lg">
        |                                         ^^^^
    327 |             Spacious layout for detailed content and forms.
    328 |           </MS365Card>
    329 |         </div>
ERROR in src/demo/Microsoft365Demo.tsx:360:24
TS2322: Type '"xs"' is not assignable to type '"md" | "sm" | "lg" | "xl" | undefined'.
    358 |         </h3>
    359 |         <div style={{ display: 'flex', flexWrap: 'wrap', gap: Microsoft365DesignSystem.spacing[3], alignItems: 'center' }}>
  > 360 |           <MS365Button size="xs">Extra Small</MS365Button>
        |                        ^^^^
    361 |           <MS365Button size="sm">Small</MS365Button>
    362 |           <MS365Button size="md">Medium</MS365Button>
    363 |           <MS365Button size="lg">Large</MS365Button>
ERROR in src/demo/Microsoft365Demo.tsx:386:24
TS2322: Type '{ children: string; leftIcon: string; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    384 |         </h3>
    385 |         <div style={{ display: 'flex', flexWrap: 'wrap', gap: Microsoft365DesignSystem.spacing[3] }}>
  > 386 |           <MS365Button leftIcon="📊">Analytics</MS365Button>
        |                        ^^^^^^^^
    387 |           <MS365Button rightIcon="🚀">Deploy</MS365Button>
    388 |           <MS365Button leftIcon="💾" rightIcon="📤">Save & Export</MS365Button>
    389 |           <MS365Button variant="success" leftIcon="✅">Approve All</MS365Button>
ERROR in src/demo/Microsoft365Demo.tsx:387:24
TS2322: Type '{ children: string; rightIcon: string; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'rightIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    385 |         <div style={{ display: 'flex', flexWrap: 'wrap', gap: Microsoft365DesignSystem.spacing[3] }}>
    386 |           <MS365Button leftIcon="📊">Analytics</MS365Button>
  > 387 |           <MS365Button rightIcon="🚀">Deploy</MS365Button>
        |                        ^^^^^^^^^
    388 |           <MS365Button leftIcon="💾" rightIcon="📤">Save & Export</MS365Button>
    389 |           <MS365Button variant="success" leftIcon="✅">Approve All</MS365Button>
    390 |           <MS365Button variant="error" leftIcon="🗑️">Delete Items</MS365Button>
ERROR in src/demo/Microsoft365Demo.tsx:388:24
TS2322: Type '{ children: string; leftIcon: string; rightIcon: string; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    386 |           <MS365Button leftIcon="📊">Analytics</MS365Button>
    387 |           <MS365Button rightIcon="🚀">Deploy</MS365Button>
  > 388 |           <MS365Button leftIcon="💾" rightIcon="📤">Save & Export</MS365Button>
        |                        ^^^^^^^^
    389 |           <MS365Button variant="success" leftIcon="✅">Approve All</MS365Button>
    390 |           <MS365Button variant="error" leftIcon="🗑️">Delete Items</MS365Button>
    391 |         </div>
ERROR in src/demo/Microsoft365Demo.tsx:389:24
TS2322: Type '"success"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    387 |           <MS365Button rightIcon="🚀">Deploy</MS365Button>
    388 |           <MS365Button leftIcon="💾" rightIcon="📤">Save & Export</MS365Button>
  > 389 |           <MS365Button variant="success" leftIcon="✅">Approve All</MS365Button>
        |                        ^^^^^^^
    390 |           <MS365Button variant="error" leftIcon="🗑️">Delete Items</MS365Button>
    391 |         </div>
    392 |       </div>
ERROR in src/demo/Microsoft365Demo.tsx:390:24
TS2322: Type '"error"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    388 |           <MS365Button leftIcon="💾" rightIcon="📤">Save & Export</MS365Button>
    389 |           <MS365Button variant="success" leftIcon="✅">Approve All</MS365Button>
  > 390 |           <MS365Button variant="error" leftIcon="🗑️">Delete Items</MS365Button>
        |                        ^^^^^^^
    391 |         </div>
    392 |       </div>
    393 |
ERROR in src/demo/Microsoft365Demo.tsx:402:34
TS2322: Type '"success"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    400 |           <MS365Button fullWidth>Full Width Primary</MS365Button>
    401 |           <MS365Button fullWidth variant="secondary">Full Width Secondary</MS365Button>
  > 402 |           <MS365Button fullWidth variant="success" leftIcon="✅">Full Width with Icon</MS365Button>
        |                                  ^^^^^^^
    403 |         </div>
    404 |       </div>
    405 |     </div>
ERROR in src/demo/Microsoft365Demo.tsx:457:13
TS2322: Type '"error" | "ghost"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
  Type '"error"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    455 |           </MS365Button>
    456 |           <MS365Button
  > 457 |             variant={activeDemo === 'superadmin' ? 'error' : 'ghost'}
        |             ^^^^^^^
    458 |             onClick={() => setActiveDemo('superadmin')}
    459 |           >
    460 |             👑 Super Admin
ERROR in src/demo/MustiAdvancedSystemsDemo.tsx:355:12
TS2322: Type '{ title: string; description: string; status: "active"; progress: number; features: string[]; metrics: { Accuracy: string; 'Response Time': string; 'Models Active': string; 'Predictions/min': string; 'Models Trained'?: undefined; 'Data Processed'?: undefined; 'Training Speed'?: undefined; 'Deployment Time'?: undefin...' is not assignable to type 'IntrinsicAttributes & DemoProps'.
  Type '{ title: string; description: string; status: "active"; progress: number; features: string[]; metrics: { Accuracy: string; 'Response Time': string; 'Models Active': string; 'Predictions/min': string; 'Models Trained'?: undefined; 'Data Processed'?: undefined; 'Training Speed'?: undefined; 'Deployment Time'?: undefin...' is not assignable to type 'DemoProps'.
    Types of property 'metrics' are incompatible.
      Type '{ Accuracy: string; 'Response Time': string; 'Models Active': string; 'Predictions/min': string; 'Models Trained'?: undefined; 'Data Processed'?: undefined; 'Training Speed'?: undefined; 'Deployment Time'?: undefined; }' is not assignable to type '{ [key: string]: string | number; }'.
        Property ''Models Trained'' is incompatible with index signature.
          Type 'undefined' is not assignable to type 'string | number'.
    353 |       <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
    354 |         {getCurrentSystems().map((system, index) => (
  > 355 |           <DemoCard key={index} {...system} />
        |            ^^^^^^^^
    356 |         ))}
    357 |       </div>
    358 |
ERROR in src/demo/SuperAdminDemo.tsx:60:24
TS2322: Type '"error"' is not assignable to type '"primary" | "secondary" | "outline" | "link" | "ghost" | "destructive" | undefined'.
    58 |       <div>
    59 |         <div style={{ position: 'fixed', top: Microsoft365DesignSystem.spacing[4], right: Microsoft365DesignSystem.spacing[4], zIndex: 1000 }}>
  > 60 |           <MS365Button variant="error" onClick={() => setShowFullPanel(false)}>
       |                        ^^^^^^^
    61 |             ✖️ Demo'yu Kapat
    62 |           </MS365Button>
    63 |         </div>
ERROR in src/demo/SuperAdminDemo.tsx:88:11
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    86 |           title="🔐 Super Admin Panel Overview"
    87 |           subtitle="Premium administrative control center with Microsoft 365 design"
  > 88 |           size="lg"
       |           ^^^^
    89 |           variant="info"
    90 |         >
    91 |           <div style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.sm, lineHeight: Microsoft365DesignSystem.typography.lineHeight.relaxed }}>
ERROR in src/demo/SuperAdminDemo.tsx:187:11
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    185 |           title="⚠️ Erişim Kontrolü"
    186 |           variant="warning"
  > 187 |           size="lg"
        |           ^^^^
    188 |         >
    189 |           <div style={{ display: 'flex', alignItems: 'center', gap: Microsoft365DesignSystem.spacing[4] }}>
    190 |             <div style={{ fontSize: '48px' }}>🔒</div>
ERROR in src/demo/SuperAdminDemo.tsx:207:11
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    205 |           title="🎬 Live Demo"
    206 |           subtitle="Interaktif Super Admin Panel deneyimi"
  > 207 |           size="lg"
        |           ^^^^
    208 |         >
    209 |           <div style={{ textAlign: 'center' }}>
    210 |             <p style={{ fontSize: Microsoft365DesignSystem.typography.fontSize.base, marginBottom: Microsoft365DesignSystem.spacing[4] }}>
ERROR in src/demo/SuperAdminDemo.tsx:218:17
TS2322: Type '{ children: string; variant: "primary"; size: "lg"; leftIcon: string; onClick: () => void; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    216 |                 variant="primary" 
    217 |                 size="lg"
  > 218 |                 leftIcon="🚀"
        |                 ^^^^^^^^
    219 |                 onClick={() => setShowFullPanel(true)}
    220 |               >
    221 |                 Super Admin Panel'i Aç
ERROR in src/demo/SuperAdminDemo.tsx:227:17
TS2322: Type '{ children: string; variant: "secondary"; size: "lg"; leftIcon: string; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    225 |                 variant="secondary" 
    226 |                 size="lg"
  > 227 |                 leftIcon="📊"
        |                 ^^^^^^^^
    228 |               >
    229 |                 Feature Walkthrough
    230 |               </MS365Button>
ERROR in src/demo/SuperAdminDemo.tsx:235:17
TS2322: Type '{ children: string; variant: "ghost"; size: "lg"; leftIcon: string; }' is not assignable to type 'IntrinsicAttributes & MS365ButtonProps'.
  Property 'leftIcon' does not exist on type 'IntrinsicAttributes & MS365ButtonProps'.
    233 |                 variant="ghost" 
    234 |                 size="lg"
  > 235 |                 leftIcon="📖"
        |                 ^^^^^^^^
    236 |               >
    237 |                 Documentation
    238 |               </MS365Button>
ERROR in src/demo/SuperAdminDemo.tsx:247:11
TS2322: Type '"lg"' is not assignable to type '"large" | "medium" | "small" | undefined'.
    245 |           title="🔧 Technical Specifications"
    246 |           subtitle="Microsoft 365 Design System implementation details"
  > 247 |           size="lg"
        |           ^^^^
    248 |         >
    249 |           <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: Microsoft365DesignSystem.spacing[4] }}>
    250 |             <div>
ERROR in src/deployment/DropshippingOptimizationDeployer.ts
TS1208: 'DropshippingOptimizationDeployer.ts' cannot be compiled under '--isolatedModules' because it is considered a global script file. Add an import, export, or an empty 'export {}' statement to make it a module.
ERROR in src/deployment/DropshippingOptimizationDeployment.ts
TS1208: 'DropshippingOptimizationDeployment.ts' cannot be compiled under '--isolatedModules' because it is considered a global script file. Add an import, export, or an empty 'export {}' statement to make it a module.
ERROR in src/deployment/test-deployment.ts:6:1
TS1208: 'test-deployment.ts' cannot be compiled under '--isolatedModules' because it is considered a global script file. Add an import, export, or an empty 'export {}' statement to make it a module.
    4 |  */
    5 |
  > 6 | console.log('🚀 MezBjen Takımı - Dropshipping Performance Optimization Test');
      | ^^^^^^^
    7 | console.log('📅 Tarih: 9 Haziran 2025, Pazartesi - 14:00-16:00 Görev Slotu');
    8 | console.log('🎯 Testing deployment system for %40+ performance improvement\n');
    9 |
ERROR in src/deployment/test-deployment.ts:13:42
TS2306: File '/Users/mezbjen/Desktop/meschain-sync-enterprise-1/src/deployment/DropshippingOptimizationDeployer.ts' is not a module.
    11 |   try {
    12 |     // Import the quick deployment function
  > 13 |     const { quickDeploy } = await import('./DropshippingOptimizationDeployer');
       |                                          ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    14 |     
    15 |     console.log('🔧 Running quick deployment test...\n');
    16 |     
ERROR in src/deployment/test-deployment.ts:40:29
TS7006: Parameter 'error' implicitly has an 'any' type.
    38 |     if (result.errors.length > 0) {
    39 |       console.log(`❌ Errors: ${result.errors.length}`);
  > 40 |       result.errors.forEach(error => console.log(`   - ${error}`));
       |                             ^^^^^
    41 |     }
    42 |     
    43 |     if (result.recommendations.length > 0) {
ERROR in src/deployment/test-deployment.ts:45:38
TS7006: Parameter 'rec' implicitly has an 'any' type.
    43 |     if (result.recommendations.length > 0) {
    44 |       console.log('\n💡 RECOMMENDATIONS:');
  > 45 |       result.recommendations.forEach(rec => console.log(`   ${rec}`));
       |                                      ^^^
    46 |     }
    47 |
    48 |     console.log('\n🎉 MezBjen dropshipping optimization test completed!');
ERROR in src/deployment/test-deployment.ts:60:19
TS18046: 'error' is of type 'unknown'.
    58 |   } catch (error) {
    59 |     console.error('\n❌ TEST FAILED:');
  > 60 |     console.error(error.message);
       |                   ^^^^^
    61 |     console.error('\nStack:', error.stack);
    62 |     process.exit(1);
    63 |   }
ERROR in src/deployment/test-deployment.ts:61:31
TS18046: 'error' is of type 'unknown'.
    59 |     console.error('\n❌ TEST FAILED:');
    60 |     console.error(error.message);
  > 61 |     console.error('\nStack:', error.stack);
       |                               ^^^^^
    62 |     process.exit(1);
    63 |   }
    64 | }
ERROR in src/dropshipping/DropshippingPerformanceOptimizer.ts
TS1208: 'DropshippingPerformanceOptimizer.ts' cannot be compiled under '--isolatedModules' because it is considered a global script file. Add an import, export, or an empty 'export {}' statement to make it a module.
ERROR in src/i18n/config.ts:231:5
TS2741: Property 'one' is missing in type '{ other: string; }' but required in type '{ zero?: string | undefined; one: string; two?: string | undefined; few?: string | undefined; many?: string | undefined; other: string; }'.
    229 |       negativeCurrencyFormat: '-{symbol}{amount}'
    230 |     },
  > 231 |     pluralRules: {
        |     ^^^^^^^^^^^
    232 |       other: 'other'
    233 |     },
    234 |     marketplaceSupport: ['amazon'],
ERROR in src/i18n/enhanced.ts:11:30
TS2307: Cannot find module 'i18next-browser-languagedetector' or its corresponding type declarations.
     9 | import i18n from 'i18next';
    10 | import { initReactI18next } from 'react-i18next';
  > 11 | import LanguageDetector from 'i18next-browser-languagedetector';
       |                              ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    12 | import Backend from 'i18next-http-backend';
    13 | import {
    14 |   SUPPORTED_LANGUAGES,
ERROR in src/i18n/enhanced.ts:12:21
TS2307: Cannot find module 'i18next-http-backend' or its corresponding type declarations.
    10 | import { initReactI18next } from 'react-i18next';
    11 | import LanguageDetector from 'i18next-browser-languagedetector';
  > 12 | import Backend from 'i18next-http-backend';
       |                     ^^^^^^^^^^^^^^^^^^^^^^
    13 | import {
    14 |   SUPPORTED_LANGUAGES,
    15 |   MARKETPLACE_LANGUAGE_CONFIG,
ERROR in src/i18n/enhanced.ts:35:14
TS2323: Cannot redeclare exported variable 'TranslationManager'.
    33 |
    34 | // Enhanced Translation Manager
  > 35 | export class TranslationManager {
       |              ^^^^^^^^^^^^^^^^^^
    36 |   private static instance: TranslationManager;
    37 |   private loadedNamespaces: Set<string> = new Set();
    38 |   private loadingPromises: Map<string, Promise<any>> = new Map();
ERROR in src/i18n/enhanced.ts:153:14
TS2323: Cannot redeclare exported variable 'LanguageManager'.
    151 |
    152 | // Enhanced Language Manager
  > 153 | export class LanguageManager {
        |              ^^^^^^^^^^^^^^^
    154 |   private static instance: LanguageManager;
    155 |   private translationManager: TranslationManager;
    156 |   private currentLanguage: string = DEFAULT_LANGUAGE;
ERROR in src/i18n/enhanced.ts:268:29
TS2341: Property 'notifyObservers' is private and only accessible within class 'TranslationManager'.
    266 |     this.currentLanguage = language;
    267 |     this.updateDocumentAttributes(language);
  > 268 |     this.translationManager.notifyObservers(language);
        |                             ^^^^^^^^^^^^^^^
    269 |   }
    270 |
    271 |   // Update document attributes for the language
ERROR in src/i18n/enhanced.ts:463:1
TS2769: No overload matches this call.
  Overload 1 of 2, '(callback?: Callback | undefined): Promise<TFunction<"translation", undefined>>', gave the following error.
    Argument of type '{ backend: { loadPath: string; addPath: string; allowMultiLoading: boolean; crossDomain: boolean; withCredentials: boolean; requestOptions: { cache: string; }; }; preload: string[]; load: string; cleanCode: boolean; ... 9 more ...; react: { ...; }; }' is not assignable to parameter of type 'Callback'.
      Object literal may only specify known properties, and 'backend' does not exist in type 'Callback'.
  Overload 2 of 2, '(options: InitOptions<{ loadPath: string; addPath: string; allowMultiLoading: boolean; crossDomain: boolean; withCredentials: boolean; requestOptions: { cache: string; }; }>, callback?: Callback | undefined): Promise<...>', gave the following error.
    Argument of type '{ backend: { loadPath: string; addPath: string; allowMultiLoading: false; crossDomain: false; withCredentials: false; requestOptions: { cache: string; }; }; preload: string[]; load: string; cleanCode: boolean; ... 9 more ...; react: { ...; }; }' is not assignable to parameter of type 'InitOptions<{ loadPath: string; addPath: string; allowMultiLoading: boolean; crossDomain: boolean; withCredentials: boolean; requestOptions: { cache: string; }; }>'.
      Types of property 'load' are incompatible.
        Type 'string' is not assignable to type '"all" | "languageOnly" | "currentOnly" | undefined'.
    461 |
    462 | // Enhanced i18n configuration
  > 463 | i18n
        | ^^^^
  > 464 |   .use(Backend)
        | ^^^^^^^^^^^^^^^
  > 465 |   .use(LanguageDetector)
        | ^^^^^^^^^^^^^^^
  > 466 |   .use(initReactI18next)
        | ^^^^^^^^^^^^^^^
  > 467 |   .init({
        | ^^^^^^^^^^^^^^^
  > 468 |     resources,
        | ^^^^^^^^^^^^^^^
  > 469 |     fallbackLng: FALLBACK_LANGUAGE,
        | ^^^^^^^^^^^^^^^
  > 470 |     debug: process.env.NODE_ENV === 'development',
        | ^^^^^^^^^^^^^^^
  > 471 |     
        | ^^^^^^^^^^^^^^^
  > 472 |     // Namespace configuration
        | ^^^^^^^^^^^^^^^
  > 473 |     ns: Object.keys(TRANSLATION_NAMESPACES),
        | ^^^^^^^^^^^^^^^
  > 474 |     defaultNS: 'translation',
        | ^^^^^^^^^^^^^^^
  > 475 |     
        | ^^^^^^^^^^^^^^^
  > 476 |     // Interpolation settings
        | ^^^^^^^^^^^^^^^
  > 477 |     interpolation: {
        | ^^^^^^^^^^^^^^^
  > 478 |       escapeValue: false, // React already escapes values
        | ^^^^^^^^^^^^^^^
  > 479 |       format: (value, format, lng) => {
        | ^^^^^^^^^^^^^^^
  > 480 |         const languageManager = LanguageManager.getInstance();
        | ^^^^^^^^^^^^^^^
  > 481 |         
        | ^^^^^^^^^^^^^^^
  > 482 |         if (format === 'number') {
        | ^^^^^^^^^^^^^^^
  > 483 |           return languageManager.formatNumber(value);
        | ^^^^^^^^^^^^^^^
  > 484 |         }
        | ^^^^^^^^^^^^^^^
  > 485 |         if (format === 'currency') {
        | ^^^^^^^^^^^^^^^
  > 486 |           return languageManager.formatCurrency(value);
        | ^^^^^^^^^^^^^^^
  > 487 |         }
        | ^^^^^^^^^^^^^^^
  > 488 |         if (format === 'date') {
        | ^^^^^^^^^^^^^^^
  > 489 |           return languageManager.formatDate(value);
        | ^^^^^^^^^^^^^^^
  > 490 |         }
        | ^^^^^^^^^^^^^^^
  > 491 |         if (format === 'percent') {
        | ^^^^^^^^^^^^^^^
  > 492 |           return languageManager.formatNumber(value, { style: 'percent' });
        | ^^^^^^^^^^^^^^^
  > 493 |         }
        | ^^^^^^^^^^^^^^^
  > 494 |         return value;
        | ^^^^^^^^^^^^^^^
  > 495 |       }
        | ^^^^^^^^^^^^^^^
  > 496 |     },
        | ^^^^^^^^^^^^^^^
  > 497 |     
        | ^^^^^^^^^^^^^^^
  > 498 |     // Language detection
        | ^^^^^^^^^^^^^^^
  > 499 |     detection: LANGUAGE_DETECTION_CONFIG,
        | ^^^^^^^^^^^^^^^
  > 500 |     
        | ^^^^^^^^^^^^^^^
  > 501 |     // React specific settings
        | ^^^^^^^^^^^^^^^
  > 502 |     react: {
        | ^^^^^^^^^^^^^^^
  > 503 |       useSuspense: true,
        | ^^^^^^^^^^^^^^^
  > 504 |       bindI18n: 'languageChanged loaded',
        | ^^^^^^^^^^^^^^^
  > 505 |       bindI18nStore: 'added removed',
        | ^^^^^^^^^^^^^^^
  > 506 |       transEmptyNodeValue: '',
        | ^^^^^^^^^^^^^^^
  > 507 |       transSupportBasicHtmlNodes: true,
        | ^^^^^^^^^^^^^^^
  > 508 |       transKeepBasicHtmlNodesFor: ['br', 'strong', 'i', 'p', 'span'],
        | ^^^^^^^^^^^^^^^
  > 509 |     },
        | ^^^^^^^^^^^^^^^
  > 510 |     
        | ^^^^^^^^^^^^^^^
  > 511 |     // Performance settings
        | ^^^^^^^^^^^^^^^
  > 512 |     ...PERFORMANCE_CONFIG,
        | ^^^^^^^^^^^^^^^
  > 513 |     
        | ^^^^^^^^^^^^^^^
  > 514 |     // Backend configuration for dynamic loading
        | ^^^^^^^^^^^^^^^
  > 515 |     backend: {
        | ^^^^^^^^^^^^^^^
  > 516 |       loadPath: '/locales/{{lng}}/{{ns}}.json',
        | ^^^^^^^^^^^^^^^
  > 517 |       addPath: '/locales/add/{{lng}}/{{ns}}',
        | ^^^^^^^^^^^^^^^
  > 518 |       allowMultiLoading: false,
        | ^^^^^^^^^^^^^^^
  > 519 |       crossDomain: false,
        | ^^^^^^^^^^^^^^^
  > 520 |       withCredentials: false,
        | ^^^^^^^^^^^^^^^
  > 521 |       requestOptions: {
        | ^^^^^^^^^^^^^^^
  > 522 |         cache: 'default'
        | ^^^^^^^^^^^^^^^
  > 523 |       }
        | ^^^^^^^^^^^^^^^
  > 524 |     },
        | ^^^^^^^^^^^^^^^
  > 525 |   });
        | ^^^^^
    526 |
    527 | // Export enhanced system
    528 | export { i18n, LanguageManager, TranslationManager };
ERROR in src/i18n/enhanced.ts:479:16
TS7006: Parameter 'value' implicitly has an 'any' type.
    477 |     interpolation: {
    478 |       escapeValue: false, // React already escapes values
  > 479 |       format: (value, format, lng) => {
        |                ^^^^^
    480 |         const languageManager = LanguageManager.getInstance();
    481 |         
    482 |         if (format === 'number') {
ERROR in src/i18n/enhanced.ts:479:23
TS7006: Parameter 'format' implicitly has an 'any' type.
    477 |     interpolation: {
    478 |       escapeValue: false, // React already escapes values
  > 479 |       format: (value, format, lng) => {
        |                       ^^^^^^
    480 |         const languageManager = LanguageManager.getInstance();
    481 |         
    482 |         if (format === 'number') {
ERROR in src/i18n/enhanced.ts:479:31
TS7006: Parameter 'lng' implicitly has an 'any' type.
    477 |     interpolation: {
    478 |       escapeValue: false, // React already escapes values
  > 479 |       format: (value, format, lng) => {
        |                               ^^^
    480 |         const languageManager = LanguageManager.getInstance();
    481 |         
    482 |         if (format === 'number') {
ERROR in src/i18n/enhanced.ts:528:16
TS2323: Cannot redeclare exported variable 'LanguageManager'.
    526 |
    527 | // Export enhanced system
  > 528 | export { i18n, LanguageManager, TranslationManager };
        |                ^^^^^^^^^^^^^^^
    529 | export * from './config';
    530 |
    531 | // Initialize the enhanced language system
ERROR in src/i18n/enhanced.ts:528:16
TS2484: Export declaration conflicts with exported declaration of 'LanguageManager'.
    526 |
    527 | // Export enhanced system
  > 528 | export { i18n, LanguageManager, TranslationManager };
        |                ^^^^^^^^^^^^^^^
    529 | export * from './config';
    530 |
    531 | // Initialize the enhanced language system
ERROR in src/i18n/enhanced.ts:528:33
TS2323: Cannot redeclare exported variable 'TranslationManager'.
    526 |
    527 | // Export enhanced system
  > 528 | export { i18n, LanguageManager, TranslationManager };
        |                                 ^^^^^^^^^^^^^^^^^^
    529 | export * from './config';
    530 |
    531 | // Initialize the enhanced language system
ERROR in src/i18n/enhanced.ts:528:33
TS2484: Export declaration conflicts with exported declaration of 'TranslationManager'.
    526 |
    527 | // Export enhanced system
  > 528 | export { i18n, LanguageManager, TranslationManager };
        |                                 ^^^^^^^^^^^^^^^^^^
    529 | export * from './config';
    530 |
    531 | // Initialize the enhanced language system
ERROR in src/i18n/index.ts:3:30
TS2307: Cannot find module 'i18next-browser-languagedetector' or its corresponding type declarations.
    1 | import i18n from 'i18next';
    2 | import { initReactI18next } from 'react-i18next';
  > 3 | import LanguageDetector from 'i18next-browser-languagedetector';
      |                              ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    4 | import Backend from 'i18next-http-backend';
    5 |
    6 | // Import translation files
ERROR in src/i18n/index.ts:4:21
TS2307: Cannot find module 'i18next-http-backend' or its corresponding type declarations.
    2 | import { initReactI18next } from 'react-i18next';
    3 | import LanguageDetector from 'i18next-browser-languagedetector';
  > 4 | import Backend from 'i18next-http-backend';
      |                     ^^^^^^^^^^^^^^^^^^^^^^
    5 |
    6 | // Import translation files
    7 | import trTranslations from './locales/tr/translation.json';
ERROR in src/index.tsx:61:7
TS2322: Type '(error: Error, errorInfo: {    componentStack: string;}) => void' is not assignable to type '(error: Error, info: ErrorInfo) => void'.
  Types of parameters 'errorInfo' and 'info' are incompatible.
    Type 'ErrorInfo' is not assignable to type '{ componentStack: string; }'.
      Types of property 'componentStack' are incompatible.
        Type 'string | null | undefined' is not assignable to type 'string'.
          Type 'undefined' is not assignable to type 'string'.
    59 |     <ErrorBoundary
    60 |       FallbackComponent={GlobalErrorFallback}
  > 61 |       onError={handleError}
       |       ^^^^^^^
    62 |       onReset={() => window.location.reload()}
    63 |     >
    64 |       <HelmetProvider>
ERROR in src/index.tsx:96:54
TS2339: Property 'hot' does not exist on type 'Module'.
    94 |
    95 | // Hot Module Replacement for development
  > 96 | if (process.env.NODE_ENV === 'development' && module.hot) {
       |                                                      ^^^
    97 |   module.hot.accept('./App', () => {
    98 |     console.log('🔄 Hot reloading App component');
    99 |   });
ERROR in src/index.tsx:97:10
TS2339: Property 'hot' does not exist on type 'Module'.
     95 | // Hot Module Replacement for development
     96 | if (process.env.NODE_ENV === 'development' && module.hot) {
  >  97 |   module.hot.accept('./App', () => {
        |          ^^^
     98 |     console.log('🔄 Hot reloading App component');
     99 |   });
    100 | }
ERROR in src/pages/AdvancedReportsPage.tsx:19:8
TS2307: Cannot find module '@heroicons/react/24/outline' or its corresponding type declarations.
    17 |   BookmarkIcon,
    18 |   PrinterIcon
  > 19 | } from '@heroicons/react/24/outline';
       |        ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    20 | import { subDays } from 'date-fns';
    21 | import toast from 'react-hot-toast';
    22 |
ERROR in src/pages/AdvancedReportsPage.tsx:21:19
TS2307: Cannot find module 'react-hot-toast' or its corresponding type declarations.
    19 | } from '@heroicons/react/24/outline';
    20 | import { subDays } from 'date-fns';
  > 21 | import toast from 'react-hot-toast';
       |                   ^^^^^^^^^^^^^^^^^
    22 |
    23 | interface FilterOptions {
    24 |   dateRange: {
ERROR in src/pages/TrendyolTestPage.tsx:13:8
TS2307: Cannot find module '@heroicons/react/24/outline' or its corresponding type declarations.
    11 |   ShoppingCartIcon,
    12 |   DocumentTextIcon
  > 13 | } from '@heroicons/react/24/outline';
       |        ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    14 |
    15 | interface ApiTestResult {
    16 |   name: string;
ERROR in src/performance/PerformanceOptimizer.ts:112:51
TS2339: Property 'navigationStart' does not exist on type 'PerformanceNavigationTiming'.
    110 |         metric = {
    111 |           name: 'Page Load Time',
  > 112 |           value: navEntry.loadEventEnd - navEntry.navigationStart,
        |                                                   ^^^^^^^^^^^^^^^
    113 |           unit: 'ms',
    114 |           timestamp: new Date(),
    115 |           category: 'loading',
ERROR in src/security/SecurityManager.tsx:160:39
TS2802: Type 'IterableIterator<[string, SecurityToken]>' can only be iterated through when using the '--downlevelIteration' flag or with a '--target' of 'es2015' or higher.
    158 |   public revokeAllUserTokens(userId: string): number {
    159 |     let revokedCount = 0;
  > 160 |     for (const [tokenValue, token] of this.tokens.entries()) {
        |                                       ^^^^^^^^^^^^^^^^^^^^^
    161 |       if (token.userId === userId) {
    162 |         this.tokens.delete(tokenValue);
    163 |         revokedCount++;
ERROR in src/security/SecurityManager.tsx:384:33
TS2802: Type 'IterableIterator<[string, RateLimitEntry]>' can only be iterated through when using the '--downlevelIteration' flag or with a '--target' of 'es2015' or higher.
    382 |     setInterval(() => {
    383 |       const now = new Date();
  > 384 |       for (const [ip, entry] of this.requests.entries()) {
        |                                 ^^^^^^^^^^^^^^^^^^^^^^^
    385 |         if (entry.resetTime <= now) {
    386 |           this.requests.delete(ip);
    387 |         }
ERROR in src/security/SecurityManager.tsx:594:24
TS2345: Argument of type '{ id: any; username: any; }' is not assignable to parameter of type 'SetStateAction<null>'.
  Object literal may only specify kwn properties, and 'id' does not exist in type '(prevState: null) => null'.
    592 |       setToken(newToken);
    593 |       setIsAuthenticated(true);
  > 594 |       setCurrentUser({ id: userId, username: credentials.username });
        |                        ^^^^^^^^^^
    595 |       setPermissions(userPermissions);
    596 |       
    597 |       return true;