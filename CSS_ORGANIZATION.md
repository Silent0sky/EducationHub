# CSS File Organization Guide

## Overview
CSS files have been separated by functionality and page type for better maintainability and modularity.

## File Structure

### Core CSS Files

#### 1. **global.css** - Global Styles & Layout
- CSS variables (colors, shadows, transitions, radius)
- HTML reset and base styles
- Sidebar navigation
- Main content layout
- Header and user info components
- Responsive breakpoints

#### 2. **common.css** - Shared UI Components
- Buttons (.btn, .btn-primary, .btn-secondary, etc.)
- Form elements (inputs, selects, textareas, form-group)
- Cards and card headers
- Tables and table styling
- Alerts (.alert-success, .alert-error, .alert-warning)
- Badges
- Empty state styling

### Page-Specific CSS Files

#### 3. **auth.css** - Authentication Pages
- Used by: `auth/login.php`, `auth/register.php`
- Contains: Auth card layout, form styling, header, footer, logo

#### 4. **dashboard.css** - Dashboard Pages
- Used by: `dashboard.php`, `admin/dashboard.php`
- Contains: Stat cards, stats grid, subjects grid, subject cards, quick actions, responsive layouts

#### 5. **admin.css** - Admin Management Pages
- Used by: `admin/subjects.php`, `admin/users.php`, `manage_questions.php`
- Contains: Admin stats, filter bar, search boxes, modals, admin-specific tables, list items, icon buttons

#### 6. **quiz.css** - Quiz Pages
- Used by: `quiz.php`
- Contains: Quiz container, question cards, options list, result cards, progress displays, score circles

#### 7. **notes.css** - Note Management Pages (Unified)
- Used by: `search_notes.php`, `upload_notes.php`, `download_notes.php`, `my_uploads.php`
- Contains:
  - Search/Filter: Year tabs, semester tabs, search bar, filter selects
  - Notes Display: Notes grid, note cards, badges, metadata
  - Upload Form: File upload area, form sections, button groups

#### 8. **performance.css** - Performance & Analytics Pages
- Used by: `performance.php`, `teacher_performance.php`
- Contains: Subject tabs, score cards, results list, progress bars, chart containers, comparison stats, filters

#### 9. **upload_notes.css** - Additional Upload Styling
- Used by: `upload_notes.php` (in addition to notes.css)
- Contains: Drag-and-drop file upload specific styling

## CSS Inclusion Strategy

### Authentication Pages (login.php, register.php)
```html
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/common.css">
<link rel="stylesheet" href="../assets/css/auth.css">
```

### Student/Teacher/Admin Dashboards (dashboard.php, admin/dashboard.php)
```html
<link rel="stylesheet" href="assets/css/global.css">        <!-- or ../assets/css for admin/ -->
<link rel="stylesheet" href="assets/css/common.css">
<link rel="stylesheet" href="assets/css/dashboard.css">
```

### Admin Management Pages (subjects.php, users.php, manage_questions.php)
```html
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/common.css">
<link rel="stylesheet" href="../assets/css/admin.css">
```

### Quiz Pages (quiz.php)
```html
<link rel="stylesheet" href="assets/css/global.css">
<link rel="stylesheet" href="assets/css/common.css">
<link rel="stylesheet" href="assets/css/quiz.css">
```

### Note Pages (search_notes.php, upload_notes.php, my_uploads.php)
```html
<link rel="stylesheet" href="assets/css/global.css">
<link rel="stylesheet" href="assets/css/common.css">
<link rel="stylesheet" href="assets/css/notes.css">
<!-- Plus upload_notes.css for upload page only -->
<link rel="stylesheet" href="assets/css/upload_notes.css">
```

### Performance Pages (performance.php, teacher_performance.php)
```html
<link rel="stylesheet" href="assets/css/global.css">
<link rel="stylesheet" href="assets/css/common.css">
<link rel="stylesheet" href="assets/css/performance.css">
```

## Benefits of This Organization

1. **Reduced File Size** - Load only necessary CSS for each page type
2. **Better Maintainability** - Find styles by functionality, not mixed all together
3. **Easier Caching** - Shared CSS (global + common) cached across all pages
4. **Modular Structure** - Easy to add new pages with existing CSS patterns
5. **Clear Separation of Concerns** - Each file has a single responsibility
6. **Responsive Design** - All files include mobile-friendly breakpoints

## File Sizes (Approximate)
- global.css: ~5 KB
- common.css: ~4 KB
- auth.css: ~2 KB
- dashboard.css: ~3 KB
- admin.css: ~4 KB
- quiz.css: ~4 KB
- notes.css: ~7 KB
- performance.css: ~6 KB
- upload_notes.css: ~2 KB (legacy, can be merged into notes.css later)

**Total**: ~37 KB (vs. old single style.css: 50+ KB)

## Migration from Old style.css

The original `style.css` (1373 lines) has been split into 9 focused CSS files:
- Variables and layout → global.css
- Components → common.css
- Auth styles → auth.css
- Dashboard styles → dashboard.css
- Admin styles → admin.css
- Quiz styles → quiz.css (kept existing)
- Notes styles → notes.css
- Performance styles → performance.css
- Upload styles → upload_notes.css (legacy)

All HTML files have been updated to use the new CSS structure.
