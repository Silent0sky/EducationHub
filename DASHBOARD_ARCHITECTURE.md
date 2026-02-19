# Dashboard Architecture - Role-Based Separation

## Overview
The Education Hub application now implements a **role-based dashboard architecture**, where each user type (Student, Teacher, Admin) has a dedicated dashboard file and styling, replacing the previous monolithic single-dashboard approach with conditional role-based rendering.

## Architecture Changes

### Previous Architecture (Deprecated)
- **Single file approach**: One `dashboard.php` contained logic for all three roles with heavy conditional statements
- **Single CSS file**: One `dashboard.css` combined styling for all role variants
- **Maintenance challenges**: Increasing complexity as more role-specific features were added

### New Architecture (Current)
- **Role-specific dashboard files**: Separate PHP files for each user type
- **Role-specific CSS files**: Separate stylesheets for each role
- **Role-based router**: Main `dashboard.php` acts as a router that redirects users to appropriate dashboard
- **Cleaner separation of concerns**: Each dashboard focuses on its specific user type

## File Structure

### Dashboard Files
```
index.php
├── /admin/dashboard.php (Admin-specific dashboard)
├── /dashboard.php (Router - redirects to role-specific dashboards)
├── /student_dashboard.php (Student-specific dashboard)
└── /teacher_dashboard.php (Teacher-specific dashboard)
```

### CSS Files
```
/assets/css/
├── global.css (Shared variables, layout, sidebar, header)
├── common.css (Shared buttons, forms, cards, tables)
├── dashboard.css (Legacy - may be deprecated)
├── dashboard_student.css (NEW - Student dashboard styling)
├── dashboard_teacher.css (NEW - Teacher dashboard styling)
└── dashboard_admin.css (NEW - Admin dashboard styling)
```

## User Flow

1. **User logs in** → `index.php`
2. **Index.php** checks user role:
   - If **Admin**: Redirects to `admin/dashboard.php`
   - If **Student/Teacher**: Redirects to `dashboard.php`
3. **dashboard.php router** checks user role:
   - If **Student**: Includes `student_dashboard.php`
   - If **Teacher**: Includes `teacher_dashboard.php`
   - If **Admin**: Includes `admin/dashboard.php`
4. **Appropriate dashboard loaded** with role-specific CSS

## Dashboard Details

### Student Dashboard (`student_dashboard.php`)
**Purpose**: Personal learning interface
**Features**:
- Welcome banner with personalized greeting
- Personal statistics: Quizzes taken, Average score, Subjects studied, Notes available
- Mini quick metrics (compact stat cards)
- Quick action buttons:
  - 📝 Search Notes
  - ❓ Take Quiz
  - 📊 View Performance
  - ⬇️ My Downloads
- Recent quiz attempts table
- Subject grid showing all available subjects for learning

**CSS File**: `dashboard_student.css`

**Key Colors**:
- Primary gradient: Blue (#0066cc)
- Success accent: Green (#10b981)
- Warning accent: Orange (#f59e0b)

**Styling Features**:
- Welcome banner with primary gradient
- Color-coded score badges (green >= 70%, orange 50-69%, red < 50%)
- Responsive grid (3 columns → 2 → 1 on mobile)
- Hover effects on subject cards with transform

### Teacher Dashboard (`teacher_dashboard.php`)
**Purpose**: Class and course management interface
**Features**:
- Welcome banner addressing teacher professionally
- Teaching statistics: Notes uploaded, Questions created, Quizzes created, Class average
- Mini metrics showing student engagement
- Quick action buttons:
  - 📤 Upload Notes
  - ➕ Add Questions
  - 📝 Manage Uploads
  - 📊 Class Performance
- Recent uploads table with file sizes
- My Subjects grid for class-specific material management

**CSS File**: `dashboard_teacher.css`

**Key Colors**:
- Primary gradient: Purple (#667eea → #764ba2)
- Success accent: Green (#10b981)
- Warning accent: Orange (#f59e0b)

**Styling Features**:
- Professional purple gradient banner
- Subject cards with top border accent
- Table styling with row hover effects
- Responsive design (3 columns → 2 → 1 on mobile)

### Admin Dashboard (`admin/dashboard.php`)
**Purpose**: System-wide platform management
**Features**:
- System overview with 3-column tiles:
  - Students count (🎓 blue accent)
  - Teachers count (👨‍🏫 purple accent)
  - Subjects count (📚 green accent)
- Full-width statistics cards for:
  - Total users, Students, Teachers, Subjects
  - Total notes, questions, quiz attempts
- Recent users table
- All uploaded notes table with subject and uploader info
- All questions table with creator and subject info

**CSS File**: `dashboard_admin.css`

**Key Features**:
- 3-column admin tiles grid (responsive to 2 columns, then 1)
- Color-coded tiles with top border accents
- Large stat cards with left-side accent bars
- Status and role badges in tables
- Action buttons for editing/deleting items

## CSS Organization

### Shared CSS Files (Unchanged)
- **global.css**: CSS variables, base layout, sidebar navigation, header bar
- **common.css**: Reusable button styles, form elements, card components, tables

### Role-Specific CSS Files (New)
Each role-specific CSS file includes:

1. **Section-specific styling**:
   - Welcome banner (different gradient per role)
   - Statistics card grid layouts
   - Mini metrics grid
   - Subject/classroom card grids
   - Table styling

2. **Responsive breakpoints**:
   - Desktop (default): Full width layouts
   - Tablet (≤768px): 2-3 column grids
   - Mobile (≤480px): Single column layout

3. **Color schemes**:
   - Student: Blue primary theme
   - Teacher: Purple primary theme
   - Admin: Multi-colored tiles (blue, purple, green)

## Key Components Explained

### Welcome Banner
```css
/* Each dashboard has a role-specific gradient */
.welcome-banner {
    background: linear-gradient(135deg, [role-primary-color] 0%, [role-accent-color] 100%);
}
/* Student: Blue gradient */
/* Teacher: Purple gradient */
```

### Stat Cards
```css
/* Base styling for statistics display */
.stat-card {
    /* Top border accent indicating metric type */
    ::before {
        background: [gradient or solid color];
    }
}
/* Success (green), Warning (orange), Default (primary) */
```

### Responsive Grids
```css
/* 4-column on desktop, 2 on tablet, 1 on mobile */
.stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}
```

## Database Queries by Role

### Student Dashboard Queries
```php
// Personal statistics
getUserStats($student_id)

// Recent quiz attempts (last 5)
SELECT * FROM quiz_results
WHERE student_id = ?
ORDER BY attempt_date DESC LIMIT 5

// Available subjects
SELECT * FROM subjects
ORDER BY year, semester, name
```

### Teacher Dashboard Queries
```php
// Notes uploaded by teacher
COUNT(*) FROM notes WHERE uploaded_by = ?

// Questions created by teacher
COUNT(*) FROM questions WHERE created_by = ?

// Students' average scores on teacher's quizzes
AVG(score/total_questions*100) FROM quiz_results
WHERE quiz_id IN (SELECT id FROM quizzes WHERE created_by = ?)

// Recent uploads
SELECT * FROM notes WHERE uploaded_by = ?
ORDER BY upload_date DESC LIMIT 5

// Teacher's subject list
SELECT DISTINCT subjects.* FROM subjects
WHERE id IN (
    SELECT subject_id FROM notes WHERE uploaded_by = ?
    UNION
    SELECT subject_id FROM quizzes WHERE created_by = ?
)
```

### Admin Dashboard Queries
```php
// System counters
COUNT(*) FROM users
COUNT(*) FROM users WHERE role = 'student'
COUNT(*) FROM users WHERE role = 'teacher'
COUNT(*) FROM subjects
COUNT(*) FROM notes
COUNT(*) FROM questions
COUNT(*) FROM quiz_results

// Recent users
SELECT * FROM users ORDER BY created_at DESC LIMIT 5

// All notes with metadata
SELECT n.*, u.name, s.name, s.color
FROM notes n
JOIN users u ON n.uploaded_by = u.id
JOIN subjects s ON n.subject_id = s.id
LIMIT 10

// All questions with metadata
SELECT q.*, u.name, s.name
FROM questions q
JOIN users u ON q.created_by = u.id
JOIN subjects s ON q.subject_id = s.id
LIMIT 10
```

## Migration Notes

### For Developers
1. **Old conditional logic** in single dashboard.php is now split into three files
2. **CSS variable naming** remains consistent (uses global.css variables)
3. **Shared utility classes** still available in common.css
4. **Legacy dashboard.css** can be deprecated or kept for historical reference

### For Maintenance
1. **To modify Student dashboard**: Edit `student_dashboard.php` and `dashboard_student.css`
2. **To modify Teacher dashboard**: Edit `teacher_dashboard.php` and `dashboard_teacher.css`
3. **To modify Admin dashboard**: Edit `admin/dashboard.php` and `dashboard_admin.css`
4. **To update shared styles**: Edit `global.css` or `common.css`

## Testing Checklist

- [ ] Student user logs in → sees student_dashboard.php
- [ ] Teacher user logs in → sees teacher_dashboard.php
- [ ] Admin user logs in → sees admin/dashboard.php
- [ ] All statistics display correctly for each role
- [ ] Responsive design works on tablet (≤768px)
- [ ] Responsive design works on mobile (≤480px)
- [ ] Color coding and badges display correctly
- [ ] Quick action buttons navigate to correct pages
- [ ] Subject/class cards are clickable and functional
- [ ] All CSS files load without errors
- [ ] No console JavaScript errors

## Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- ES6 JavaScript (if used)
- CSS Grid and Flexbox required
- Mobile-first responsive design

## Performance Considerations
- Each dashboard loads only role-specific CSS (smaller file size)
- Shared CSS cached by browser (global.css, common.css)
- Database queries optimized with LIMIT clauses
- No unnecessary includes or external dependencies

---

**Last Updated**: Current session
**Architecture Version**: 2.0 (Role-based separation)
**Previous Version**: 1.0 (Monolithic single dashboard)