/**
 * Advanced Search & Filtering System
 * Features: Real-time search, smart suggestions, multi-criteria filtering, saved searches
 */

import React, { useState, useCallback, useMemo, useEffect, useRef } from 'react';
import {
  TextField,
  Autocomplete,
  Chip,
  Button,
  Menu,
  MenuItem,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  FormControl,
  InputLabel,
  Select,
  Slider,
  DatePicker,
  Checkbox,
  FormControlLabel,
  IconButton,
  Tooltip,
  Badge,
  Collapse,
  Box,
  Typography,
  Card,
  CardContent,
  Divider,
  List,
  ListItem,
  ListItemText,
  ListItemIcon
} from '@mui/material';
import {
  Search as SearchIcon,
  FilterList as FilterIcon,
  Clear as ClearIcon,
  Save as SaveIcon,
  History as HistoryIcon,
  Star as StarIcon,
  StarBorder as StarBorderIcon,
  ExpandMore as ExpandMoreIcon,
  ExpandLess as ExpandLessIcon,
  Close as CloseIcon,
  TrendingUp as TrendingUpIcon,
  AccessTime as AccessTimeIcon
} from '@mui/icons-material';
import { debounce } from 'lodash';

// Types
export interface SearchFilter {
  id: string;
  type: 'text' | 'select' | 'multiSelect' | 'range' | 'date' | 'dateRange' | 'boolean' | 'tags';
  label: string;
  field: string;
  options?: Array<{ value: any; label: string; count?: number }>;
  min?: number;
  max?: number;
  defaultValue?: any;
  required?: boolean;
  placeholder?: string;
  description?: string;
  group?: string;
}

export interface SearchQuery {
  text: string;
  filters: Record<string, any>;
  sort?: {
    field: string;
    direction: 'asc' | 'desc';
  };
  limit?: number;
  offset?: number;
}

export interface SavedSearch {
  id: string;
  name: string;
  query: SearchQuery;
  createdAt: Date;
  updatedAt: Date;
  favorite: boolean;
  category?: string;
  description?: string;
  shared?: boolean;
}

export interface SearchSuggestion {
  type: 'recent' | 'popular' | 'suggestion' | 'filter';
  text: string;
  count?: number;
  category?: string;
  filters?: Record<string, any>;
}

interface AdvancedSearchProps {
  filters: SearchFilter[];
  onSearch: (query: SearchQuery) => void;
  onGetSuggestions?: (text: string) => Promise<SearchSuggestion[]>;
  savedSearches?: SavedSearch[];
  onSaveSavedSearch?: (search: Omit<SavedSearch, 'id' | 'createdAt' | 'updatedAt'>) => void;
  onDeleteSavedSearch?: (id: string) => void;
  onUpdateSavedSearch?: (id: string, updates: Partial<SavedSearch>) => void;
  placeholder?: string;
  showFilterCount?: boolean;
  showSavedSearches?: boolean;
  showSearchHistory?: boolean;
  maxSuggestions?: number;
  debounceMs?: number;
  className?: string;
  style?: React.CSSProperties;
}

export const AdvancedSearch: React.FC<AdvancedSearchProps> = ({
  filters = [],
  onSearch,
  onGetSuggestions,
  savedSearches = [],
  onSaveSavedSearch,
  onDeleteSavedSearch,
  onUpdateSavedSearch,
  placeholder = "Search...",
  showFilterCount = true,
  showSavedSearches = true,
  showSearchHistory = true,
  maxSuggestions = 10,
  debounceMs = 300,
  className,
  style
}) => {
  // State
  const [searchText, setSearchText] = useState('');
  const [activeFilters, setActiveFilters] = useState<Record<string, any>>({});
  const [suggestions, setSuggestions] = useState<SearchSuggestion[]>([]);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [showFilters, setShowFilters] = useState(false);
  const [searchHistory, setSearchHistory] = useState<string[]>([]);
  const [filterAnchorEl, setFilterAnchorEl] = useState<null | HTMLElement>(null);
  const [saveDialogOpen, setSaveDialogOpen] = useState(false);
  const [saveSearchName, setSaveSearchName] = useState('');
  const [saveSearchCategory, setSaveSearchCategory] = useState('');
  const [expandedGroups, setExpandedGroups] = useState<Set<string>>(new Set());
  const [recentSearches, setRecentSearches] = useState<SearchQuery[]>([]);
  
  const searchInputRef = useRef<HTMLInputElement>(null);
  const suggestionsRef = useRef<HTMLDivElement>(null);

  // Load search history from localStorage
  useEffect(() => {
    const savedHistory = localStorage.getItem('meschain_search_history');
    const savedRecent = localStorage.getItem('meschain_recent_searches');
    
    if (savedHistory) {
      try {
        setSearchHistory(JSON.parse(savedHistory));
      } catch (error) {
        console.warn('Failed to parse search history:', error);
      }
    }
    
    if (savedRecent) {
      try {
        setRecentSearches(JSON.parse(savedRecent));
      } catch (error) {
        console.warn('Failed to parse recent searches:', error);
      }
    }
  }, []);

  // Debounced suggestion fetching
  const debouncedGetSuggestions = useCallback(
    debounce(async (text: string) => {
      if (!text.trim() || !onGetSuggestions) {
        setSuggestions([]);
        return;
      }

      try {
        const results = await onGetSuggestions(text);
        setSuggestions(results.slice(0, maxSuggestions));
      } catch (error) {
        console.error('Failed to fetch suggestions:', error);
        setSuggestions([]);
      }
    }, debounceMs),
    [onGetSuggestions, maxSuggestions, debounceMs]
  );

  // Handle search text change
  const handleSearchTextChange = (value: string) => {
    setSearchText(value);
    debouncedGetSuggestions(value);
    setShowSuggestions(true);
  };

  // Handle search submission
  const handleSearch = useCallback((customQuery?: Partial<SearchQuery>) => {
    const query: SearchQuery = {
      text: searchText.trim(),
      filters: { ...activeFilters },
      ...customQuery
    };

    // Add to search history
    if (query.text) {
      const newHistory = [query.text, ...searchHistory.filter(h => h !== query.text)].slice(0, 20);
      setSearchHistory(newHistory);
      localStorage.setItem('meschain_search_history', JSON.stringify(newHistory));
    }

    // Add to recent searches
    const newRecent = [query, ...recentSearches.filter(r => 
      JSON.stringify(r) !== JSON.stringify(query)
    )].slice(0, 10);
    setRecentSearches(newRecent);
    localStorage.setItem('meschain_recent_searches', JSON.stringify(newRecent));

    setShowSuggestions(false);
    onSearch(query);
  }, [searchText, activeFilters, searchHistory, recentSearches, onSearch]);

  // Handle filter change
  const handleFilterChange = (filterId: string, value: any) => {
    setActiveFilters(prev => {
      const newFilters = { ...prev };
      if (value === undefined || value === null || value === '' || 
          (Array.isArray(value) && value.length === 0)) {
        delete newFilters[filterId];
      } else {
        newFilters[filterId] = value;
      }
      return newFilters;
    });
  };

  // Clear all filters
  const clearFilters = () => {
    setActiveFilters({});
  };

  // Clear search
  const clearSearch = () => {
    setSearchText('');
    setSuggestions([]);
    setShowSuggestions(false);
  };

  // Save current search
  const handleSaveSearch = () => {
    if (!onSaveSavedSearch || !saveSearchName.trim()) return;

    const savedSearch: Omit<SavedSearch, 'id' | 'createdAt' | 'updatedAt'> = {
      name: saveSearchName.trim(),
      query: {
        text: searchText.trim(),
        filters: { ...activeFilters }
      },
      favorite: false,
      category: saveSearchCategory || undefined
    };

    onSaveSavedSearch(savedSearch);
    setSaveDialogOpen(false);
    setSaveSearchName('');
    setSaveSearchCategory('');
  };

  // Load saved search
  const handleLoadSavedSearch = (savedSearch: SavedSearch) => {
    setSearchText(savedSearch.query.text);
    setActiveFilters(savedSearch.query.filters);
    handleSearch(savedSearch.query);
  };

  // Toggle saved search favorite
  const handleToggleFavorite = (id: string) => {
    if (!onUpdateSavedSearch) return;
    const savedSearch = savedSearches.find(s => s.id === id);
    if (savedSearch) {
      onUpdateSavedSearch(id, { favorite: !savedSearch.favorite });
    }
  };

  // Group filters by group
  const groupedFilters = useMemo(() => {
    const groups: Record<string, SearchFilter[]> = {};
    filters.forEach(filter => {
      const group = filter.group || 'General';
      if (!groups[group]) groups[group] = [];
      groups[group].push(filter);
    });
    return groups;
  }, [filters]);

  // Count active filters
  const activeFilterCount = Object.keys(activeFilters).length;

  // Generate suggestions including history and recent searches
  const allSuggestions = useMemo(() => {
    const historySuggestions: SearchSuggestion[] = showSearchHistory 
      ? searchHistory
          .filter(h => h.toLowerCase().includes(searchText.toLowerCase()))
          .slice(0, 3)
          .map(h => ({ type: 'recent', text: h }))
      : [];

    const recentSuggestions: SearchSuggestion[] = recentSearches
      .filter(r => r.text.toLowerCase().includes(searchText.toLowerCase()))
      .slice(0, 2)
      .map(r => ({ type: 'suggestion', text: r.text, filters: r.filters }));

    return [...historySuggestions, ...recentSuggestions, ...suggestions]
      .slice(0, maxSuggestions);
  }, [suggestions, searchHistory, recentSearches, searchText, showSearchHistory, maxSuggestions]);

  // Render filter component
  const renderFilter = (filter: SearchFilter) => {
    const value = activeFilters[filter.id];

    switch (filter.type) {
      case 'text':
        return (
          <TextField
            key={filter.id}
            label={filter.label}
            placeholder={filter.placeholder}
            value={value || ''}
            onChange={(e) => handleFilterChange(filter.id, e.target.value)}
            size="small"
            fullWidth
            helperText={filter.description}
          />
        );

      case 'select':
        return (
          <FormControl key={filter.id} size="small" fullWidth>
            <InputLabel>{filter.label}</InputLabel>
            <Select
              value={value || ''}
              onChange={(e) => handleFilterChange(filter.id, e.target.value)}
              label={filter.label}
            >
              <MenuItem value="">
                <em>None</em>
              </MenuItem>
              {filter.options?.map(option => (
                <MenuItem key={option.value} value={option.value}>
                  {option.label}
                  {option.count && (
                    <Typography variant="body2" color="textSecondary" sx={{ ml: 1 }}>
                      ({option.count})
                    </Typography>
                  )}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        );

      case 'multiSelect':
        return (
          <Autocomplete
            key={filter.id}
            multiple
            options={filter.options || []}
            getOptionLabel={(option) => option.label}
            value={filter.options?.filter(opt => (value || []).includes(opt.value)) || []}
            onChange={(_, newValue) => 
              handleFilterChange(filter.id, newValue.map(v => v.value))
            }
            renderInput={(params) => (
              <TextField
                {...params}
                label={filter.label}
                placeholder={filter.placeholder}
                size="small"
              />
            )}
            renderTags={(tagValue, getTagProps) =>
              tagValue.map((option, index) => (
                <Chip
                  variant="outlined"
                  label={option.label}
                  size="small"
                  {...getTagProps({ index })}
                />
              ))
            }
          />
        );

      case 'range':
        return (
          <Box key={filter.id}>
            <Typography gutterBottom>{filter.label}</Typography>
            <Slider
              value={value || [filter.min || 0, filter.max || 100]}
              onChange={(_, newValue) => handleFilterChange(filter.id, newValue)}
              valueLabelDisplay="auto"
              min={filter.min || 0}
              max={filter.max || 100}
              size="small"
            />
          </Box>
        );

      case 'boolean':
        return (
          <FormControlLabel
            key={filter.id}
            control={
              <Checkbox
                checked={value || false}
                onChange={(e) => handleFilterChange(filter.id, e.target.checked)}
                size="small"
              />
            }
            label={filter.label}
          />
        );

      case 'tags':
        return (
          <Autocomplete
            key={filter.id}
            multiple
            freeSolo
            options={filter.options?.map(opt => opt.label) || []}
            value={value || []}
            onChange={(_, newValue) => handleFilterChange(filter.id, newValue)}
            renderInput={(params) => (
              <TextField
                {...params}
                label={filter.label}
                placeholder={filter.placeholder}
                size="small"
              />
            )}
            renderTags={(tagValue, getTagProps) =>
              tagValue.map((option, index) => (
                <Chip
                  variant="outlined"
                  label={option}
                  size="small"
                  {...getTagProps({ index })}
                />
              ))
            }
          />
        );

      default:
        return null;
    }
  };

  return (
    <Box className={className} style={style}>
      {/* Main search bar */}
      <Box sx={{ display: 'flex', gap: 1, mb: 2, position: 'relative' }}>
        <TextField
          ref={searchInputRef}
          value={searchText}
          onChange={(e) => handleSearchTextChange(e.target.value)}
          onKeyPress={(e) => {
            if (e.key === 'Enter') {
              handleSearch();
            }
          }}
          onFocus={() => setShowSuggestions(true)}
          placeholder={placeholder}
          size="small"
          fullWidth
          InputProps={{
            startAdornment: <SearchIcon sx={{ mr: 1, color: 'text.secondary' }} />,
            endAdornment: searchText && (
              <IconButton size="small" onClick={clearSearch}>
                <ClearIcon />
              </IconButton>
            )
          }}
        />

        <Badge badgeContent={activeFilterCount} color="primary">
          <Button
            variant="outlined"
            onClick={(e) => setFilterAnchorEl(e.currentTarget)}
            startIcon={<FilterIcon />}
            size="small"
          >
            Filters
          </Button>
        </Badge>

        <Button
          variant="contained"
          onClick={() => handleSearch()}
          startIcon={<SearchIcon />}
          size="small"
        >
          Search
        </Button>

        {showSavedSearches && (
          <Button
            variant="outlined"
            onClick={() => setSaveDialogOpen(true)}
            startIcon={<SaveIcon />}
            size="small"
            disabled={!searchText && activeFilterCount === 0}
          >
            Save
          </Button>
        )}

        {/* Search suggestions */}
        {showSuggestions && allSuggestions.length > 0 && (
          <Card
            ref={suggestionsRef}
            sx={{
              position: 'absolute',
              top: '100%',
              left: 0,
              right: 0,
              zIndex: 1000,
              mt: 1,
              maxHeight: 300,
              overflow: 'auto'
            }}
          >
            <List dense>
              {allSuggestions.map((suggestion, index) => (
                <ListItem
                  key={index}
                  button
                  onClick={() => {
                    if (suggestion.type === 'suggestion' && suggestion.filters) {
                      setSearchText(suggestion.text);
                      setActiveFilters(suggestion.filters);
                      handleSearch({ text: suggestion.text, filters: suggestion.filters });
                    } else {
                      setSearchText(suggestion.text);
                      handleSearch({ text: suggestion.text });
                    }
                  }}
                >
                  <ListItemIcon>
                    {suggestion.type === 'recent' ? (
                      <AccessTimeIcon fontSize="small" />
                    ) : suggestion.type === 'popular' ? (
                      <TrendingUpIcon fontSize="small" />
                    ) : (
                      <SearchIcon fontSize="small" />
                    )}
                  </ListItemIcon>
                  <ListItemText
                    primary={suggestion.text}
                    secondary={suggestion.category}
                  />
                  {suggestion.count && (
                    <Typography variant="body2" color="textSecondary">
                      {suggestion.count}
                    </Typography>
                  )}
                </ListItem>
              ))}
            </List>
          </Card>
        )}
      </Box>

      {/* Active filters display */}
      {activeFilterCount > 0 && (
        <Box sx={{ display: 'flex', gap: 1, mb: 2, flexWrap: 'wrap', alignItems: 'center' }}>
          <Typography variant="body2" color="textSecondary">
            Active filters:
          </Typography>
          {Object.entries(activeFilters).map(([filterId, value]) => {
            const filter = filters.find(f => f.id === filterId);
            if (!filter) return null;

            let displayValue = String(value);
            if (Array.isArray(value)) {
              displayValue = value.join(', ');
            } else if (filter.options) {
              const option = filter.options.find(opt => opt.value === value);
              displayValue = option ? option.label : String(value);
            }

            return (
              <Chip
                key={filterId}
                label={`${filter.label}: ${displayValue}`}
                onDelete={() => handleFilterChange(filterId, undefined)}
                size="small"
                variant="outlined"
              />
            );
          })}
          <Button
            size="small"
            onClick={clearFilters}
            startIcon={<ClearIcon />}
          >
            Clear all
          </Button>
        </Box>
      )}

      {/* Saved searches */}
      {showSavedSearches && savedSearches.length > 0 && (
        <Box sx={{ mb: 2 }}>
          <Typography variant="h6" gutterBottom>
            Saved Searches
          </Typography>
          <Box sx={{ display: 'flex', gap: 1, flexWrap: 'wrap' }}>
            {savedSearches
              .filter(s => s.favorite)
              .slice(0, 5)
              .map(savedSearch => (
                <Chip
                  key={savedSearch.id}
                  label={savedSearch.name}
                  onClick={() => handleLoadSavedSearch(savedSearch)}
                  onDelete={() => onDeleteSavedSearch?.(savedSearch.id)}
                  deleteIcon={<StarIcon />}
                  variant="outlined"
                  color="primary"
                />
              ))}
          </Box>
        </Box>
      )}

      {/* Filter menu */}
      <Menu
        anchorEl={filterAnchorEl}
        open={Boolean(filterAnchorEl)}
        onClose={() => setFilterAnchorEl(null)}
        PaperProps={{
          sx: { width: 400, maxHeight: 500 }
        }}
      >
        <Box sx={{ p: 2 }}>
          <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 2 }}>
            <Typography variant="h6">Filters</Typography>
            <IconButton size="small" onClick={() => setFilterAnchorEl(null)}>
              <CloseIcon />
            </IconButton>
          </Box>

          {Object.entries(groupedFilters).map(([groupName, groupFilters]) => (
            <Box key={groupName} sx={{ mb: 2 }}>
              <Button
                onClick={() => {
                  const newExpanded = new Set(expandedGroups);
                  if (newExpanded.has(groupName)) {
                    newExpanded.delete(groupName);
                  } else {
                    newExpanded.add(groupName);
                  }
                  setExpandedGroups(newExpanded);
                }}
                startIcon={expandedGroups.has(groupName) ? <ExpandLessIcon /> : <ExpandMoreIcon />}
                sx={{ textTransform: 'none', justifyContent: 'flex-start', width: '100%' }}
              >
                {groupName}
              </Button>
              
              <Collapse in={expandedGroups.has(groupName)} timeout="auto" unmountOnExit>
                <Box sx={{ pl: 2, display: 'flex', flexDirection: 'column', gap: 2 }}>
                  {groupFilters.map(renderFilter)}
                </Box>
              </Collapse>
              
              {Object.keys(groupedFilters).indexOf(groupName) < Object.keys(groupedFilters).length - 1 && (
                <Divider sx={{ my: 1 }} />
              )}
            </Box>
          ))}

          <Box sx={{ display: 'flex', gap: 1, mt: 2 }}>
            <Button
              variant="contained"
              onClick={() => {
                setFilterAnchorEl(null);
                handleSearch();
              }}
              size="small"
              fullWidth
            >
              Apply Filters
            </Button>
            <Button
              variant="outlined"
              onClick={clearFilters}
              size="small"
            >
              Clear
            </Button>
          </Box>
        </Box>
      </Menu>

      {/* Save search dialog */}
      <Dialog open={saveDialogOpen} onClose={() => setSaveDialogOpen(false)}>
        <DialogTitle>Save Search</DialogTitle>
        <DialogContent>
          <TextField
            autoFocus
            margin="dense"
            label="Search Name"
            fullWidth
            variant="outlined"
            value={saveSearchName}
            onChange={(e) => setSaveSearchName(e.target.value)}
            sx={{ mb: 2 }}
          />
          <TextField
            margin="dense"
            label="Category (optional)"
            fullWidth
            variant="outlined"
            value={saveSearchCategory}
            onChange={(e) => setSaveSearchCategory(e.target.value)}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setSaveDialogOpen(false)}>Cancel</Button>
          <Button onClick={handleSaveSearch} variant="contained">
            Save
          </Button>
        </DialogActions>
      </Dialog>

      {/* Click outside to close suggestions */}
      {showSuggestions && (
        <Box
          onClick={() => setShowSuggestions(false)}
          sx={{
            position: 'fixed',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            zIndex: 999
          }}
        />
      )}
    </Box>
  );
};

export default AdvancedSearch; 