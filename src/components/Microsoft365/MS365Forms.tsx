/**
 * MS365Forms - Reactive Form System
 * Microsoft 365 design system compliant form components
 * 
 * @version 2.0.0
 * @author MesChain Sync Team
 */

import React, { useState, useRef, useEffect, useCallback } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../../theme/microsoft365-advanced';

// Form Field Types
export type FieldType = 'text' | 'email' | 'password' | 'number' | 'tel' | 'url' | 'search' | 'textarea' | 'select' | 'checkbox' | 'radio' | 'file' | 'date' | 'time' | 'datetime-local';

// Validation Rules
export interface ValidationRule {
  required?: boolean;
  minLength?: number;
  maxLength?: number;
  min?: number;
  max?: number;
  pattern?: RegExp;
  custom?: (value: any) => string | null;
}

// Form Field Configuration
export interface FormField {
  name: string;
  label: string;
  type: FieldType;
  placeholder?: string;
  defaultValue?: any;
  options?: Array<{ value: any; label: string; disabled?: boolean }>;
  validation?: ValidationRule;
  disabled?: boolean;
  required?: boolean;
  helpText?: string;
  icon?: React.ReactNode;
  size?: 'sm' | 'md' | 'lg';
  fullWidth?: boolean;
  multiple?: boolean;
  accept?: string; // for file inputs
  rows?: number; // for textarea
}

// Form State
export interface FormState {
  values: Record<string, any>;
  errors: Record<string, string>;
  touched: Record<string, boolean>;
  isSubmitting: boolean;
  isValid: boolean;
}

// Form Props
export interface MS365FormProps {
  fields: FormField[];
  onSubmit: (values: Record<string, any>) => Promise<void> | void;
  onValidate?: (values: Record<string, any>) => Record<string, string>;
  initialValues?: Record<string, any>;
  layout?: 'vertical' | 'horizontal' | 'grid';
  columns?: 1 | 2 | 3 | 4;
  spacing?: 'sm' | 'md' | 'lg';
  submitText?: string;
  cancelText?: string;
  showCancel?: boolean;
  onCancel?: () => void;
  loading?: boolean;
  disabled?: boolean;
  className?: string;
  style?: React.CSSProperties;
}

// Input Component Props
export interface MS365InputProps {
  name: string;
  label?: string;
  type?: FieldType;
  value?: any;
  placeholder?: string;
  disabled?: boolean;
  required?: boolean;
  error?: string;
  helpText?: string;
  icon?: React.ReactNode;
  size?: 'sm' | 'md' | 'lg';
  fullWidth?: boolean;
  onChange?: (value: any) => void;
  onBlur?: () => void;
  onFocus?: () => void;
  options?: Array<{ value: any; label: string; disabled?: boolean }>;
  multiple?: boolean;
  accept?: string;
  rows?: number;
  className?: string;
  style?: React.CSSProperties;
}

// Form validation function
const validateField = (value: any, rules: ValidationRule): string | null => {
  if (rules.required && (!value || (typeof value === 'string' && value.trim() === ''))) {
    return 'This field is required';
  }

  if (value && typeof value === 'string') {
    if (rules.minLength && value.length < rules.minLength) {
      return `Minimum length is ${rules.minLength} characters`;
    }

    if (rules.maxLength && value.length > rules.maxLength) {
      return `Maximum length is ${rules.maxLength} characters`;
    }

    if (rules.pattern && !rules.pattern.test(value)) {
      return 'Invalid format';
    }
  }

  if (value && typeof value === 'number') {
    if (rules.min !== undefined && value < rules.min) {
      return `Minimum value is ${rules.min}`;
    }

    if (rules.max !== undefined && value > rules.max) {
      return `Maximum value is ${rules.max}`;
    }
  }

  if (rules.custom) {
    return rules.custom(value);
  }

  return null;
};

// Input Field Component
export const MS365Input: React.FC<MS365InputProps> = ({
  name,
  label,
  type = 'text',
  value = '',
  placeholder,
  disabled = false,
  required = false,
  error,
  helpText,
  icon,
  size = 'md',
  fullWidth = true,
  onChange,
  onBlur,
  onFocus,
  options = [],
  multiple = false,
  accept,
  rows = 3,
  className,
  style
}) => {
  const [isFocused, setIsFocused] = useState(false);
  const inputRef = useRef<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>(null);

  // Size styles
  const getSizeStyles = () => {
    switch (size) {
      case 'sm':
        return {
          padding: `${MS365Spacing[2]} ${MS365Spacing[3]}`,
          fontSize: MS365Typography.sizes.sm,
          minHeight: '32px'
        };
      case 'lg':
        return {
          padding: `${MS365Spacing[4]} ${MS365Spacing[5]}`,
          fontSize: MS365Typography.sizes.lg,
          minHeight: '48px'
        };
      default:
        return {
          padding: `${MS365Spacing[3]} ${MS365Spacing[4]}`,
          fontSize: MS365Typography.sizes.base,
          minHeight: '40px'
        };
    }
  };

  // Input styles
  const inputStyles: React.CSSProperties = {
    ...getSizeStyles(),
    width: fullWidth ? '100%' : 'auto',
    border: `2px solid ${error ? MS365Colors.primary.red[400] : isFocused ? MS365Colors.primary.blue[500] : MS365Colors.neutral[300]}`,
    borderRadius: AdvancedMS365Theme.components.forms.borderRadius,
    backgroundColor: disabled ? MS365Colors.neutral[100] : MS365Colors.background.primary,
    color: disabled ? MS365Colors.neutral[500] : MS365Colors.neutral[900],
    fontFamily: MS365Typography.fonts.system,
    outline: 'none',
    transition: 'all 0.2s ease',
    paddingLeft: icon ? '40px' : getSizeStyles().padding.split(' ')[1],
    ...style
  };

  // Label styles
  const labelStyles: React.CSSProperties = {
    display: 'block',
    fontSize: MS365Typography.sizes.sm,
    fontWeight: MS365Typography.weights.medium,
    color: MS365Colors.neutral[700],
    marginBottom: MS365Spacing[2],
    fontFamily: MS365Typography.fonts.system
  };

  // Container styles
  const containerStyles: React.CSSProperties = {
    position: 'relative',
    marginBottom: MS365Spacing[4],
    width: fullWidth ? '100%' : 'auto'
  };

  // Icon styles
  const iconStyles: React.CSSProperties = {
    position: 'absolute',
    left: MS365Spacing[3],
    top: '50%',
    transform: 'translateY(-50%)',
    color: MS365Colors.neutral[500],
    fontSize: size === 'sm' ? '14px' : size === 'lg' ? '20px' : '16px',
    pointerEvents: 'none'
  };

  // Error/Help text styles
  const textStyles: React.CSSProperties = {
    fontSize: MS365Typography.sizes.xs,
    marginTop: MS365Spacing[1],
    color: error ? MS365Colors.primary.red[600] : MS365Colors.neutral[600],
    lineHeight: MS365Typography.lineHeights.normal
  };

  // Event handlers
  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    let newValue: any = e.target.value;

    if (type === 'number') {
      newValue = parseFloat(newValue) || 0;
    } else if (type === 'checkbox') {
      newValue = (e.target as HTMLInputElement).checked;
    } else if (type === 'file') {
      const files = (e.target as HTMLInputElement).files;
      newValue = multiple ? Array.from(files || []) : files?.[0] || null;
    } else if (type === 'select' && multiple) {
      const select = e.target as HTMLSelectElement;
      newValue = Array.from(select.selectedOptions).map(option => option.value);
    }

    onChange?.(newValue);
  };

  const handleFocus = () => {
    setIsFocused(true);
    onFocus?.();
  };

  const handleBlur = () => {
    setIsFocused(false);
    onBlur?.();
  };

  // Render different input types
  const renderInput = () => {
    const commonProps = {
      ref: inputRef as any,
      id: name,
      name,
      disabled,
      required,
      style: inputStyles,
      onChange: handleChange,
      onFocus: handleFocus,
      onBlur: handleBlur,
      placeholder,
      'aria-invalid': !!error,
      'aria-describedby': error ? `${name}-error` : helpText ? `${name}-help` : undefined
    };

    switch (type) {
      case 'textarea':
        return (
          <textarea
            {...commonProps}
            value={value}
            rows={rows}
          />
        );

      case 'select':
        return (
          <select
            {...commonProps}
            value={value}
            multiple={multiple}
          >
            {placeholder && (
              <option value="" disabled>
                {placeholder}
              </option>
            )}
            {options.map((option, index) => (
              <option
                key={index}
                value={option.value}
                disabled={option.disabled}
              >
                {option.label}
              </option>
            ))}
          </select>
        );

      case 'checkbox':
        return (
          <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing[2] }}>
            <input
              {...commonProps}
              type="checkbox"
              checked={value}
              style={{
                width: '16px',
                height: '16px',
                accentColor: MS365Colors.primary.blue[500]
              }}
            />
            {label && (
              <label htmlFor={name} style={{ fontSize: MS365Typography.sizes.sm, color: MS365Colors.neutral[700] }}>
                {label}
                {required && <span style={{ color: MS365Colors.primary.red[500] }}>*</span>}
              </label>
            )}
          </div>
        );

      case 'radio':
        return (
          <div style={{ display: 'flex', flexDirection: 'column', gap: MS365Spacing[2] }}>
            {options.map((option, index) => (
              <div key={index} style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing[2] }}>
                <input
                  type="radio"
                  id={`${name}-${index}`}
                  name={name}
                  value={option.value}
                  checked={value === option.value}
                  disabled={disabled || option.disabled}
                  onChange={handleChange}
                  style={{
                    width: '16px',
                    height: '16px',
                    accentColor: MS365Colors.primary.blue[500]
                  }}
                />
                <label
                  htmlFor={`${name}-${index}`}
                  style={{
                    fontSize: MS365Typography.sizes.sm,
                    color: MS365Colors.neutral[700],
                    cursor: disabled || option.disabled ? 'not-allowed' : 'pointer'
                  }}
                >
                  {option.label}
                </label>
              </div>
            ))}
          </div>
        );

      case 'file':
        return (
          <input
            {...commonProps}
            type="file"
            multiple={multiple}
            accept={accept}
            style={{
              ...inputStyles,
              cursor: 'pointer'
            }}
          />
        );

      default:
        return (
          <input
            {...commonProps}
            type={type}
            value={value}
          />
        );
    }
  };

  return (
    <div className={`ms365-input ${className || ''}`} style={containerStyles}>
      {/* Label */}
      {label && type !== 'checkbox' && (
        <label htmlFor={name} style={labelStyles}>
          {label}
          {required && <span style={{ color: MS365Colors.primary.red[500] }}>*</span>}
        </label>
      )}

      {/* Input with icon */}
      <div style={{ position: 'relative' }}>
        {icon && <div style={iconStyles}>{icon}</div>}
        {renderInput()}
      </div>

      {/* Error or help text */}
      {(error || helpText) && (
        <div id={error ? `${name}-error` : `${name}-help`} style={textStyles}>
          {error || helpText}
        </div>
      )}
    </div>
  );
};

// Main Form Component
export const MS365Form: React.FC<MS365FormProps> = ({
  fields,
  onSubmit,
  onValidate,
  initialValues = {},
  layout = 'vertical',
  columns = 1,
  spacing = 'md',
  submitText = 'Submit',
  cancelText = 'Cancel',
  showCancel = false,
  onCancel,
  loading = false,
  disabled = false,
  className,
  style
}) => {
  // Form state
  const [formState, setFormState] = useState<FormState>({
    values: { ...initialValues },
    errors: {},
    touched: {},
    isSubmitting: false,
    isValid: true
  });

  // Validate form
  const validateForm = useCallback(() => {
    const errors: Record<string, string> = {};

    // Field validation
    fields.forEach(field => {
      if (field.validation) {
        const error = validateField(formState.values[field.name], field.validation);
        if (error) {
          errors[field.name] = error;
        }
      }
    });

    // Custom validation
    if (onValidate) {
      const customErrors = onValidate(formState.values);
      Object.assign(errors, customErrors);
    }

    return errors;
  }, [fields, formState.values, onValidate]);

  // Update form validity
  useEffect(() => {
    const errors = validateForm();
    setFormState(prev => ({
      ...prev,
      errors,
      isValid: Object.keys(errors).length === 0
    }));
  }, [validateForm]);

  // Handle field change
  const handleFieldChange = (name: string, value: any) => {
    setFormState(prev => ({
      ...prev,
      values: { ...prev.values, [name]: value },
      touched: { ...prev.touched, [name]: true }
    }));
  };

  // Handle field blur
  const handleFieldBlur = (name: string) => {
    setFormState(prev => ({
      ...prev,
      touched: { ...prev.touched, [name]: true }
    }));
  };

  // Handle form submit
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (disabled || loading || formState.isSubmitting) return;

    // Mark all fields as touched
    const touched: Record<string, boolean> = {};
    fields.forEach(field => {
      touched[field.name] = true;
    });

    setFormState(prev => ({ ...prev, touched, isSubmitting: true }));

    // Validate form
    const errors = validateForm();
    if (Object.keys(errors).length > 0) {
      setFormState(prev => ({ ...prev, errors, isSubmitting: false }));
      return;
    }

    try {
      await onSubmit(formState.values);
    } catch (error) {
      console.error('Form submission error:', error);
    } finally {
      setFormState(prev => ({ ...prev, isSubmitting: false }));
    }
  };

  // Form styles
  const formStyles: React.CSSProperties = {
    width: '100%',
    fontFamily: MS365Typography.fonts.system,
    ...style
  };

  // Grid styles
  const gridStyles: React.CSSProperties = {
    display: layout === 'grid' ? 'grid' : layout === 'horizontal' ? 'flex' : 'block',
    gridTemplateColumns: layout === 'grid' ? `repeat(${columns}, 1fr)` : undefined,
    gap: spacing === 'sm' ? MS365Spacing[3] : spacing === 'lg' ? MS365Spacing[6] : MS365Spacing[4],
    flexWrap: layout === 'horizontal' ? 'wrap' : undefined,
    alignItems: layout === 'horizontal' ? 'flex-start' : undefined
  };

  // Button styles
  const buttonContainerStyles: React.CSSProperties = {
    display: 'flex',
    gap: MS365Spacing[3],
    marginTop: MS365Spacing[6],
    justifyContent: layout === 'horizontal' ? 'flex-start' : 'flex-end'
  };

  return (
    <form
      className={`ms365-form ${className || ''}`}
      style={formStyles}
      onSubmit={handleSubmit}
      noValidate
    >
      <div style={gridStyles}>
        {fields.map(field => (
          <MS365Input
            key={field.name}
            name={field.name}
            label={field.label}
            type={field.type}
            value={formState.values[field.name] || field.defaultValue || ''}
            placeholder={field.placeholder}
            disabled={disabled || field.disabled}
            required={field.required}
            error={formState.touched[field.name] ? formState.errors[field.name] : undefined}
            helpText={field.helpText}
            icon={field.icon}
            size={field.size}
            fullWidth={field.fullWidth}
            options={field.options}
            multiple={field.multiple}
            accept={field.accept}
            rows={field.rows}
            onChange={(value) => handleFieldChange(field.name, value)}
            onBlur={() => handleFieldBlur(field.name)}
            style={{
              gridColumn: layout === 'grid' && field.fullWidth ? '1 / -1' : undefined
            }}
          />
        ))}
      </div>

      {/* Form Actions */}
      <div style={buttonContainerStyles}>
        {showCancel && (
          <button
            type="button"
            onClick={onCancel}
            disabled={disabled || formState.isSubmitting}
            style={{
              padding: `${MS365Spacing[3]} ${MS365Spacing[6]}`,
              border: `2px solid ${MS365Colors.neutral[300]}`,
              borderRadius: AdvancedMS365Theme.components.forms.borderRadius,
              backgroundColor: 'transparent',
              color: MS365Colors.neutral[700],
              fontSize: MS365Typography.sizes.base,
              fontWeight: MS365Typography.weights.medium,
              cursor: 'pointer',
              transition: 'all 0.2s ease'
            }}
            onMouseOver={(e) => {
              e.currentTarget.style.backgroundColor = MS365Colors.neutral[100];
            }}
            onMouseOut={(e) => {
              e.currentTarget.style.backgroundColor = 'transparent';
            }}
          >
            {cancelText}
          </button>
        )}

        <button
          type="submit"
          disabled={disabled || formState.isSubmitting || !formState.isValid}
          style={{
            padding: `${MS365Spacing[3]} ${MS365Spacing[6]}`,
            border: 'none',
            borderRadius: AdvancedMS365Theme.components.forms.borderRadius,
            backgroundColor: formState.isValid ? MS365Colors.primary.blue[500] : MS365Colors.neutral[400],
            color: MS365Colors.neutral[50],
            fontSize: MS365Typography.sizes.base,
            fontWeight: MS365Typography.weights.medium,
            cursor: formState.isValid ? 'pointer' : 'not-allowed',
            transition: 'all 0.2s ease',
            opacity: formState.isSubmitting ? 0.7 : 1,
            display: 'flex',
            alignItems: 'center',
            gap: MS365Spacing[2]
          }}
          onMouseOver={(e) => {
            if (formState.isValid) {
              e.currentTarget.style.backgroundColor = MS365Colors.primary.blue[600];
            }
          }}
          onMouseOut={(e) => {
            if (formState.isValid) {
              e.currentTarget.style.backgroundColor = MS365Colors.primary.blue[500];
            }
          }}
        >
          {formState.isSubmitting && (
            <div
              style={{
                width: '16px',
                height: '16px',
                border: '2px solid transparent',
                borderTop: '2px solid currentColor',
                borderRadius: '50%',
                animation: 'spin 1s linear infinite'
              }}
            />
          )}
          {formState.isSubmitting ? 'Submitting...' : submitText}
        </button>
      </div>
    </form>
  );
};

export default MS365Form; 