# Memorial App v0.8.0 - Context Package
## Simplified Memorial Content Management System

### Project Overview
Memorial application focused on providing essential memorial/funeral service information with simple content management. This is **NOT** a complex CMS but a focused tool for families to manage memorial content.

### Current Status (v0.5.0 → v0.8.0)
- ✅ Basic application structure with authentication
- ✅ Gallery, wishes, posts, tasks functionality
- ✅ Admin dashboard with basic stats
- 🎯 **v0.8.0 Goal**: Streamlined admin UI + memorial-specific content management

### Core Memorial Content Requirements

#### Memorial Events (4 Types)
1. **Funeral Service** - Main service details
2. **Viewing/Visitation** - Wake/viewing information
3. **Burial/Cemetery** - Graveside service
4. **Repass/Reception** - Post-service gathering

**Each Event Includes:**
- Date & Time
- Venue Name & Address
- Contact Information (phone/email)
- Notes/Special Instructions
- Event Poster/Image Upload

#### Memorial Content
- **Biography** - Life story of deceased
- **Memorial Details** - Name, birth/death dates, key info
- **Contact Information** - Family contact details for inquiries

#### Updates & Announcements
- **Text Updates** - News, announcements, thank you messages
- **File Attachments** - Programs, documents, links
- **External Links** - Livestreams, donation pages
- **Pinned Updates** - Important announcements

### Database Schema Design

#### 1. Memorial Events Table
```sql
CREATE TABLE memorial_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_type ENUM('funeral', 'viewing', 'burial', 'repass') NOT NULL,
    title VARCHAR(255),
    date DATE,
    time TIME,
    venue_name VARCHAR(255),
    address TEXT,
    contact_phone VARCHAR(50),
    contact_email VARCHAR(255),
    notes TEXT,
    poster_media_id BIGINT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (poster_media_id) REFERENCES media(id) ON DELETE SET NULL
);
```

#### 2. Memorial Content Table
```sql
CREATE TABLE memorial_content (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    content_type VARCHAR(100) NOT NULL,
    title VARCHAR(255),
    content TEXT,
    updated_at TIMESTAMP,
    UNIQUE KEY unique_content_type (content_type)
);
```

**Content Types:**
- `bio` - Biography text
- `memorial_name` - Full name of deceased
- `memorial_dates` - Birth/death dates
- `contact_info` - Family contact information

#### 3. Memorial Updates Table
```sql
CREATE TABLE memorial_updates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    attachment_urls JSON,
    is_pinned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Admin Interface Design

#### Simplified Sidebar Navigation
```
📊 Dashboard
📅 Memorial Events
    ├── Funeral Service
    ├── Viewing/Visitation
    ├── Burial/Cemetery
    └── Repass/Reception
📝 Memorial Content
    ├── Biography & Details
    ├── Contact Information
    └── Updates & Announcements
💌 Wishes & Messages (existing)
🖼️ Gallery (existing)
📋 Tasks (existing)
📚 Documentation (existing)
```

#### Key UI Principles
- **Memorial-Focused** - Every feature serves memorial needs
- **Family-Friendly** - Easy for non-technical family members
- **Mobile-First** - Works well on phones/tablets
- **Single-Purpose** - Each page has one clear function

### Implementation Strategy: TDD/Stubs/Review/Refine Cycle

#### Phase 1: Foundation (Day 1)
**TDD Approach:**
1. **Write Tests** for admin layout and navigation
2. **Create Stubs** for new admin layout structure
3. **Review** layout with existing admin pages
4. **Refine** responsive design and navigation

**Deliverables:**
- New admin layout with memorial-focused sidebar
- Updated existing admin views to use new layout
- Mobile-responsive navigation

**Checkpoint:** Admin layout functional with existing features

#### Phase 2: Memorial Events (Day 2)
**TDD Approach:**
1. **Write Tests** for MemorialEvent model and controller
2. **Create Stubs** for events CRUD operations
3. **Review** event management workflow
4. **Refine** user experience and validation

**Deliverables:**
- MemorialEvent migration, model, controller
- Events management interface (4 event type cards)
- Poster upload functionality

**Checkpoint:** Can manage all 4 memorial event types

#### Phase 3: Memorial Content (Day 3)
**TDD Approach:**
1. **Write Tests** for MemorialContent model and controller
2. **Create Stubs** for content editing interface
3. **Review** content management workflow
4. **Refine** editor experience and validation

**Deliverables:**
- MemorialContent migration, model, controller
- Bio/details/contact editing interface
- Integration with homepage display

**Checkpoint:** Homepage content is now admin-manageable

#### Phase 4: Updates System (Day 4)
**TDD Approach:**
1. **Write Tests** for MemorialUpdate model and controller
2. **Create Stubs** for updates CRUD interface
3. **Review** updates management workflow
4. **Refine** attachment handling and display

**Deliverables:**
- MemorialUpdate migration, model, controller
- Updates management interface (blog-like)
- File attachment and external link support

**Checkpoint:** Admin can post updates with attachments

#### Phase 5: Integration & Polish (Day 5)
**TDD Approach:**
1. **Write Tests** for complete user workflows
2. **Create Stubs** for any missing integrations
3. **Review** entire admin experience
4. **Refine** performance, validation, UX

**Deliverables:**
- Complete homepage integration
- Error handling and validation
- Mobile experience optimization
- Documentation updates

**Final Checkpoint:** Memorial v0.8.0 complete and tested

### Technical Architecture

#### Controllers
- `MemorialEventsController` - Manage 4 event types
- `MemorialContentController` - Bio, details, contact info
- `MemorialUpdatesController` - Text updates with attachments

#### Models
- `MemorialEvent` - Event details with poster image
- `MemorialContent` - Flexible content storage
- `MemorialUpdate` - Updates with JSON attachments

#### Views Structure
```
resources/views/
├── layouts/
│   └── admin.blade.php (new streamlined admin layout)
├── admin/
│   ├── memorial/
│   │   ├── events/
│   │   │   └── index.blade.php (4 event cards)
│   │   ├── content/
│   │   │   └── index.blade.php (bio/details editor)
│   │   └── updates/
│   │       ├── index.blade.php (updates list)
│   │       └── create.blade.php (new update form)
│   └── dashboard.blade.php (updated with new layout)
└── home.blade.php (updated to use database content)
```

### Success Criteria for v0.8.0

#### Functional Requirements
- ✅ Admin can manage all 4 memorial event types
- ✅ Admin can edit biography and memorial details
- ✅ Admin can post updates with attachments
- ✅ Homepage displays database-driven content
- ✅ All features work on mobile devices

#### Technical Requirements
- ✅ Test coverage for all new functionality
- ✅ Responsive admin interface
- ✅ Proper validation and error handling
- ✅ Integration with existing media system
- ✅ Database migrations are reversible

#### User Experience Requirements
- ✅ Intuitive navigation for family members
- ✅ Clear content organization
- ✅ Mobile-friendly admin interface
- ✅ Fast page load times
- ✅ Accessible design principles

### Quality Assurance Checklist

#### Before Each Checkpoint
- [ ] All tests passing
- [ ] Code reviewed for memorial-specific needs
- [ ] Mobile experience tested
- [ ] Error handling verified
- [ ] Performance acceptable

#### Final Release Checklist
- [ ] All features tested end-to-end
- [ ] Homepage content migration completed
- [ ] Admin documentation updated
- [ ] Database backup procedures verified
- [ ] Mobile admin experience polished

### Post-v0.8.0 Considerations

#### Potential Future Enhancements
- Online guestbook integration
- Email notifications for updates
- Social media sharing
- Print-friendly memorial programs
- Multi-language support

#### Maintenance Considerations
- Regular content backups
- Image optimization
- Performance monitoring
- User feedback collection

---

**Note:** This context package serves as the single source of truth for Memorial v0.8.0 development. All implementation decisions should align with the simplified, memorial-focused approach outlined here.