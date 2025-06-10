// 📁 MesChain-Sync Enterprise - Data Export/Import Utilities
// Comprehensive data handling for business intelligence

import * as XLSX from 'xlsx';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import { saveAs } from 'file-saver';
import Papa from 'papaparse';

// ====================================
// 🎯 TYPES & INTERFACES
// ====================================

export interface ExportOptions {
  format: 'excel' | 'csv' | 'pdf' | 'json';
  filename?: string;
  includeCharts?: boolean;
  dateRange?: {
    start: Date;
    end: Date;
  };
  filters?: Record<string, any>;
  customColumns?: string[];
}

export interface ImportOptions {
  format: 'excel' | 'csv' | 'json';
  validateData?: boolean;
  mapping?: Record<string, string>;
  transformData?: (data: any) => any;
}

export interface ExportData {
  metrics?: any[];
  trends?: any[];
  segments?: any[];
  insights?: any[];
  metadata?: {
    exportDate: string;
    totalRecords: number;
    filters: any;
    version: string;
  };
}

// ====================================
// 📊 EXCEL EXPORT UTILITIES
// ====================================

export class ExcelExporter {
  private workbook: XLSX.WorkBook;
  
  constructor() {
    this.workbook = XLSX.utils.book_new();
  }

  /**
   * Add metrics data to Excel workbook
   */
  addMetricsSheet(metrics: any[], sheetName = 'Metrics') {
    const worksheet = XLSX.utils.json_to_sheet(metrics);
    
    // Style headers
    const range = XLSX.utils.decode_range(worksheet['!ref'] || 'A1');
    for (let col = range.s.c; col <= range.e.c; col++) {
      const cellAddress = XLSX.utils.encode_cell({ r: 0, c: col });
      if (worksheet[cellAddress]) {
        worksheet[cellAddress].s = {
          font: { bold: true, color: { rgb: "FFFFFF" } },
          fill: { bgColor: { indexed: 64 }, fgColor: { rgb: "0066CC" } }
        };
      }
    }

    // Auto-size columns
    const colWidths = metrics.length > 0 
      ? Object.keys(metrics[0]).map(key => ({ wch: Math.max(key.length, 15) }))
      : [];
    worksheet['!cols'] = colWidths;

    XLSX.utils.book_append_sheet(this.workbook, worksheet, sheetName);
    return this;
  }

  /**
   * Add trend data with charts
   */
  addTrendsSheet(trends: any[], sheetName = 'Trends') {
    const worksheet = XLSX.utils.json_to_sheet(trends);
    
    // Add sparklines for trend visualization
    if (trends.length > 0) {
      const lastCol = Object.keys(trends[0]).length;
      trends.forEach((_, rowIndex) => {
        const cellAddress = XLSX.utils.encode_cell({ r: rowIndex + 1, c: lastCol });
        worksheet[cellAddress] = {
          f: `SPARKLINE(B${rowIndex + 2}:${XLSX.utils.encode_col(lastCol - 1)}${rowIndex + 2})`,
          t: 'str'
        };
      });
    }

    XLSX.utils.book_append_sheet(this.workbook, worksheet, sheetName);
    return this;
  }

  /**
   * Add pivot table summary
   */
  addSummarySheet(data: any[], groupBy: string, sumBy: string, sheetName = 'Summary') {
    const pivotData = this.createPivotTable(data, groupBy, sumBy);
    const worksheet = XLSX.utils.json_to_sheet(pivotData);
    
    XLSX.utils.book_append_sheet(this.workbook, worksheet, sheetName);
    return this;
  }

  /**
   * Create pivot table from data
   */
  private createPivotTable(data: any[], groupBy: string, sumBy: string) {
    const grouped = data.reduce((acc, item) => {
      const key = item[groupBy];
      if (!acc[key]) {
        acc[key] = { [groupBy]: key, [sumBy]: 0, count: 0 };
      }
      acc[key][sumBy] += item[sumBy] || 0;
      acc[key].count += 1;
      return acc;
    }, {});

    return Object.values(grouped).map((item: any) => ({
      ...item,
      average: item[sumBy] / item.count
    }));
  }

  /**
   * Export workbook to file
   */
  export(filename = 'analytics-report.xlsx') {
    XLSX.writeFile(this.workbook, filename);
  }

  /**
   * Get workbook as buffer for further processing
   */
  getBuffer(): ArrayBuffer {
    return XLSX.write(this.workbook, { bookType: 'xlsx', type: 'array' });
  }
}

// ====================================
// 📄 PDF EXPORT UTILITIES
// ====================================

export class PDFExporter {
  private doc: jsPDF;
  private pageNumber = 1;

  constructor(orientation: 'portrait' | 'landscape' = 'portrait') {
    this.doc = new jsPDF(orientation, 'mm', 'a4');
  }

  /**
   * Add header with logo and title
   */
  addHeader(title: string, subtitle?: string) {
    this.doc.setFontSize(20);
    this.doc.setFont('helvetica', 'bold');
    this.doc.text(title, 20, 30);

    if (subtitle) {
      this.doc.setFontSize(12);
      this.doc.setFont('helvetica', 'normal');
      this.doc.text(subtitle, 20, 40);
    }

    // Add date
    this.doc.setFontSize(10);
    this.doc.text(`Generated: ${new Date().toLocaleDateString()}`, 20, 50);

    return this;
  }

  /**
   * Add metrics summary table
   */
  addMetricsTable(metrics: any[], startY = 60) {
    const tableData = metrics.map(metric => [
      metric.name || metric.title,
      this.formatValue(metric.value, metric.format),
      `${metric.change > 0 ? '+' : ''}${metric.change?.toFixed(1)}%`
    ]);

    autoTable(this.doc, {
      head: [['Metric', 'Value', 'Change']],
      body: tableData,
      startY,
      theme: 'striped',
      headStyles: { fillColor: [0, 102, 204] },
      styles: { fontSize: 10 },
    });

    return this;
  }

  /**
   * Add chart placeholder (real charts would need chart library integration)
   */
  addChartPlaceholder(title: string, data: any[], startY?: number) {
    const finalY = startY || (this.doc as any).lastAutoTable?.finalY + 20 || 100;
    
    // Chart title
    this.doc.setFontSize(14);
    this.doc.setFont('helvetica', 'bold');
    this.doc.text(title, 20, finalY);

    // Chart placeholder box
    this.doc.setDrawColor(200, 200, 200);
    this.doc.rect(20, finalY + 5, 170, 80);
    
    // Placeholder text
    this.doc.setFontSize(10);
    this.doc.setFont('helvetica', 'italic');
    this.doc.text('Chart data visualization', 95, finalY + 50, { align: 'center' });

    return this;
  }

  /**
   * Add insights section
   */
  addInsights(insights: any[], startY?: number) {
    const finalY = startY || (this.doc as any).lastAutoTable?.finalY + 100 || 200;
    
    this.doc.setFontSize(16);
    this.doc.setFont('helvetica', 'bold');
    this.doc.text('Key Insights', 20, finalY);

    let currentY = finalY + 10;
    insights.forEach((insight, index) => {
      if (currentY > 250) {
        this.doc.addPage();
        currentY = 30;
        this.pageNumber++;
      }

      // Insight bullet
      this.doc.setFontSize(12);
      this.doc.setFont('helvetica', 'bold');
      this.doc.text(`${index + 1}. ${insight.title}`, 20, currentY);

      // Insight description
      this.doc.setFont('helvetica', 'normal');
      this.doc.setFontSize(10);
      const splitText = this.doc.splitTextToSize(insight.description, 170);
      this.doc.text(splitText, 25, currentY + 5);

      currentY += splitText.length * 5 + 10;
    });

    return this;
  }

  /**
   * Add footer with page numbers
   */
  addFooter() {
    const pageCount = this.doc.getNumberOfPages();
    
    for (let i = 1; i <= pageCount; i++) {
      this.doc.setPage(i);
      this.doc.setFontSize(10);
      this.doc.text(
        `Page ${i} of ${pageCount}`, 
        this.doc.internal.pageSize.width - 30, 
        this.doc.internal.pageSize.height - 10
      );
    }

    return this;
  }

  /**
   * Format values based on type
   */
  private formatValue(value: any, format?: string): string {
    if (typeof value === 'number') {
      switch (format) {
        case 'currency':
          return new Intl.NumberFormat('tr-TR', { 
            style: 'currency', 
            currency: 'TRY' 
          }).format(value);
        case 'percentage':
          return `${value.toFixed(1)}%`;
        default:
          return value.toLocaleString();
      }
    }
    return String(value);
  }

  /**
   * Export PDF
   */
  export(filename = 'analytics-report.pdf') {
    this.addFooter();
    this.doc.save(filename);
  }

  /**
   * Get PDF as blob
   */
  getBlob(): Blob {
    this.addFooter();
    return this.doc.output('blob');
  }
}

// ====================================
// 📥 IMPORT UTILITIES
// ====================================

export class DataImporter {
  /**
   * Import Excel file
   */
  static async importExcel(file: File, options: ImportOptions = { format: 'excel' }): Promise<any[]> {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      
      reader.onload = (e) => {
        try {
          const data = new Uint8Array(e.target?.result as ArrayBuffer);
          const workbook = XLSX.read(data, { type: 'array' });
          
          // Get first sheet
          const sheetName = workbook.SheetNames[0];
          const worksheet = workbook.Sheets[sheetName];
          
          // Convert to JSON
          let jsonData = XLSX.utils.sheet_to_json(worksheet);
          
          // Apply transformations
          if (options.transformData) {
            jsonData = jsonData.map(options.transformData);
          }
          
          // Validate data if required
          if (options.validateData) {
            jsonData = this.validateData(jsonData);
          }
          
          resolve(jsonData);
        } catch (error) {
          reject(new Error(`Excel import failed: ${error instanceof Error ? error.message : 'Unknown error'}`));
        }
      };
      
      reader.onerror = () => reject(new Error('File reading failed'));
      reader.readAsArrayBuffer(file);
    });
  }

  /**
   * Import CSV file
   */
  static async importCSV(file: File, options: ImportOptions = { format: 'csv' }): Promise<any[]> {
    return new Promise((resolve, reject) => {
      Papa.parse(file, {
        header: true,
        complete: (results) => {
          try {
            let data = results.data;
            
            // Apply transformations
            if (options.transformData) {
              data = data.map(options.transformData);
            }
            
            // Validate data if required
            if (options.validateData) {
              data = this.validateData(data);
            }
            
            resolve(data);
          } catch (error) {
            reject(new Error(`CSV import failed: ${error instanceof Error ? error.message : 'Unknown error'}`));
          }
        },
        error: (error) => reject(new Error(`CSV parsing failed: ${error.message}`))
      });
    });
  }

  /**
   * Import JSON file
   */
  static async importJSON(file: File, options: ImportOptions = { format: 'json' }): Promise<any[]> {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      
      reader.onload = (e) => {
        try {
          let data = JSON.parse(e.target?.result as string);
          
          // Ensure array format
          if (!Array.isArray(data)) {
            data = [data];
          }
          
          // Apply transformations
          if (options.transformData) {
            data = data.map(options.transformData);
          }
          
          // Validate data if required
          if (options.validateData) {
            data = this.validateData(data);
          }
          
          resolve(data);
        } catch (error) {
          reject(new Error(`JSON import failed: ${error instanceof Error ? error.message : 'Unknown error'}`));
        }
      };
      
      reader.onerror = () => reject(new Error('File reading failed'));
      reader.readAsText(file);
    });
  }

  /**
   * Validate imported data
   */
  private static validateData(data: any[]): any[] {
    return data.filter(item => {
      // Basic validation - remove empty rows
      return Object.values(item).some(value => 
        value !== null && value !== undefined && value !== ''
      );
    });
  }
}

// ====================================
// 🚀 HIGH-LEVEL EXPORT FUNCTIONS
// ====================================

/**
 * Export analytics data in specified format
 */
export async function exportAnalyticsData(
  data: ExportData, 
  options: ExportOptions
): Promise<void> {
  const filename = options.filename || `analytics-${Date.now()}`;
  
  switch (options.format) {
    case 'excel':
      await exportToExcel(data, filename);
      break;
    case 'csv':
      await exportToCSV(data, filename);
      break;
    case 'pdf':
      await exportToPDF(data, filename, options.includeCharts);
      break;
    case 'json':
      await exportToJSON(data, filename);
      break;
    default:
      throw new Error(`Unsupported export format: ${options.format}`);
  }
}

/**
 * Export to Excel with multiple sheets
 */
async function exportToExcel(data: ExportData, filename: string): Promise<void> {
  const exporter = new ExcelExporter();
  
  if (data.metrics) {
    exporter.addMetricsSheet(data.metrics, 'Metrics');
  }
  
  if (data.trends) {
    exporter.addTrendsSheet(data.trends, 'Trends');
  }
  
  if (data.segments) {
    data.segments.forEach((segment, index) => {
      exporter.addMetricsSheet(segment, `Segment_${index + 1}`);
    });
  }
  
  exporter.export(`${filename}.xlsx`);
}

/**
 * Export to CSV (metrics only)
 */
async function exportToCSV(data: ExportData, filename: string): Promise<void> {
  const csvData = data.metrics || [];
  const csv = Papa.unparse(csvData);
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  saveAs(blob, `${filename}.csv`);
}

/**
 * Export to PDF with charts
 */
async function exportToPDF(data: ExportData, filename: string, includeCharts = true): Promise<void> {
  const exporter = new PDFExporter();
  
  exporter
    .addHeader('Analytics Report', 'MesChain-Sync Enterprise')
    .addMetricsTable(data.metrics || []);
  
  if (includeCharts && data.trends) {
    exporter.addChartPlaceholder('Revenue Trends', data.trends);
  }
  
  if (data.insights) {
    exporter.addInsights(data.insights);
  }
  
  exporter.export(`${filename}.pdf`);
}

/**
 * Export to JSON
 */
async function exportToJSON(data: ExportData, filename: string): Promise<void> {
  const jsonString = JSON.stringify(data, null, 2);
  const blob = new Blob([jsonString], { type: 'application/json' });
  saveAs(blob, `${filename}.json`);
}

/**
 * Import data from file
 */
export async function importAnalyticsData(
  file: File, 
  options: ImportOptions
): Promise<any[]> {
  const extension = file.name.split('.').pop()?.toLowerCase();
  
  switch (extension) {
    case 'xlsx':
    case 'xls':
      return DataImporter.importExcel(file, options);
    case 'csv':
      return DataImporter.importCSV(file, options);
    case 'json':
      return DataImporter.importJSON(file, options);
    default:
      throw new Error(`Unsupported file format: ${extension}`);
  }
}

export default {
  ExcelExporter,
  PDFExporter,
  DataImporter,
  exportAnalyticsData,
  importAnalyticsData,
}; 