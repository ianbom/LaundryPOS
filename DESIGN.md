# DESIGN.md — Laundry POS Admin Dashboard

## 1. Design Objective

This document defines the complete visual and interaction design rules for recreating the Laundry POS Admin Dashboard shown in the reference image. The dashboard must look like a clean, modern, SaaS-style point-of-sale administration panel for a laundry business. The interface should feel professional, operational, fast, and easy to use for both admin and staff users.

The dashboard uses a light theme with a white sidebar, white cards, subtle blue accents, soft shadows, thin borders, rounded corners, and a spacious grid layout. The overall visual language is clean, calm, business-oriented, and highly readable.

Primary design goals:

- Make operational data easy to scan.
- Keep all important actions visible.
- Use consistent components across all pages.
- Prioritize table readability and status clarity.
- Make the dashboard suitable for long daily usage.
- Keep the same design system for admin and staff, with only access and visible menus changing by role.

---

## 2. Global Visual Style

### 2.1 Overall Look and Feel

The interface uses a modern SaaS dashboard style with a POS-operational layout. It should not feel decorative, playful, or overly colorful. The dashboard must feel structured, reliable, and production-ready.

Visual characteristics:

- Light background.
- White cards and panels.
- Soft gray borders.
- Blue as the primary action color.
- Rounded card corners.
- Small but clear icons.
- Compact yet readable sidebar.
- Large content area with dense but organized data.
- Subtle shadows for depth.
- Strong hierarchy between title, sections, cards, and table rows.

### 2.2 Design Keywords

Use these keywords as the design direction:

- Clean
- Modern
- Minimal
- Professional
- SaaS dashboard
- POS dashboard
- Operational
- Fast scanning
- Light mode
- Rounded UI
- Soft shadow
- Blue accent
- High readability
- Structured sidebar

---

## 3. Canvas and Page Size

The reference design is shown in a wide desktop layout.

Recommended base canvas:

- Width: `2048px`
- Height: `1152px`
- Desktop-first layout
- Minimum desktop width: `1440px`
- Main dashboard optimized for large screens

Main page structure:

```txt
+--------------------------------------------------------------------------------+
| Sidebar 270px | Topbar 64px                                                    |
|               |----------------------------------------------------------------|
|               | Main Content Area                                               |
|               |                                                                |
+--------------------------------------------------------------------------------+
```

---

## 4. Color Palette

### 4.1 Primary Colors

| Usage | Color | HEX |
|---|---:|---|
| Primary Blue | Main action button, active menu, links, chart line | `#2563EB` |
| Bright Blue | Hover state, active highlights | `#1D4ED8` |
| Soft Blue | Active menu background, blue icon backgrounds | `#EAF2FF` |
| Pale Blue | Light card status background | `#EFF6FF` |
| Deep Navy | Main headings and strong text | `#0F172A` |
| Body Text | Normal readable text | `#334155` |
| Muted Text | Secondary descriptions | `#64748B` |
| Light Muted Text | Helper text / metadata | `#94A3B8` |

### 4.2 Neutral Colors

| Usage | Color | HEX |
|---|---:|---|
| App Background | Main page background | `#F8FAFC` |
| Sidebar Background | Sidebar panel | `#FFFFFF` |
| Card Background | Cards and table containers | `#FFFFFF` |
| Topbar Background | Top navigation bar | `#FFFFFF` |
| Border | Default border | `#E2E8F0` |
| Stronger Border | Input/card border | `#CBD5E1` |
| Divider | Subtle dividers | `#EEF2F7` |
| Table Header | Table header background | `#FFFFFF` |
| Hover Row | Table row hover | `#F8FAFC` |

### 4.3 Semantic Colors

| Status | Main Color | Background | Text |
|---|---:|---:|---:|
| Success / Paid / Completed | `#16A34A` | `#DCFCE7` | `#15803D` |
| Warning / Pending | `#F59E0B` | `#FEF3C7` | `#D97706` |
| Danger / Failed / Cancelled | `#EF4444` | `#FEE2E2` | `#DC2626` |
| Info / Ready for Pickup | `#06B6D4` | `#CFFAFE` | `#0891B2` |
| Processing Blue | `#2563EB` | `#DBEAFE` | `#1D4ED8` |
| Purple / Active Orders / Ironing | `#8B5CF6` | `#EDE9FE` | `#7C3AED` |
| Orange / Drying / Pending Payment | `#F59E0B` | `#FEF3C7` | `#D97706` |

### 4.4 Icon Background Colors

Use soft pastel square icon containers:

| Card Type | Background | Icon Color |
|---|---:|---:|
| Revenue | `#DCFCE7` | `#16A34A` |
| Today Orders | `#DBEAFE` | `#2563EB` |
| Active Orders | `#EDE9FE` | `#8B5CF6` |
| Ready Pickup | `#CFFAFE` | `#06B6D4` |
| Pending Payments | `#FEF3C7` | `#F59E0B` |
| Completed Orders | `#DCFCE7` | `#16A34A` |
| Cancelled | `#FEE2E2` | `#EF4444` |

---

## 5. Typography

### 5.1 Font Family

Use a modern rounded sans-serif font. The closest match to the reference is:

```css
font-family: "Inter", "Plus Jakarta Sans", "Manrope", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
```

Recommended primary font:

- `Inter`

Alternative fonts:

- `Plus Jakarta Sans`
- `Manrope`
- `Satoshi`

### 5.2 Typography Scale

| Element | Size | Weight | Line Height | Color |
|---|---:|---:|---:|---:|
| App logo text | `20px` | `700` | `28px` | `#0F172A` |
| Page title | `30px` | `700` | `38px` | `#0F172A` |
| Page subtitle | `14px` | `400` | `22px` | `#64748B` |
| Section title | `18px` | `700` | `26px` | `#0F172A` |
| Card label | `14px` | `500` | `20px` | `#64748B` |
| Card value | `26px` | `700` | `34px` | `#0F172A` |
| Trend text | `12px` | `500` | `18px` | `#64748B` |
| Sidebar section label | `10px` | `700` | `14px` | `#64748B` |
| Sidebar menu text | `13px` | `500` | `18px` | `#334155` |
| Active sidebar text | `13px` | `700` | `18px` | `#2563EB` |
| Table header | `12px` | `700` | `16px` | `#0F172A` |
| Table body | `13px` | `500` | `18px` | `#0F172A` |
| Table secondary | `12px` | `400` | `16px` | `#64748B` |
| Badge text | `12px` | `700` | `16px` | status color |
| Button text | `14px` | `700` | `20px` | varies |

### 5.3 Text Rendering Rules

- Use tight but readable line heights.
- Use bold values for metrics.
- Use muted text for metadata and descriptions.
- Avoid oversized typography except for the main page title and metric values.
- Sidebar text should be compact and easy to scan.

---

## 6. Global Layout

### 6.1 Main Shell

The dashboard consists of three main layout regions:

1. Fixed left sidebar.
2. Fixed topbar aligned to the right of sidebar.
3. Scrollable main content area.

```txt
body
└── app-shell
    ├── sidebar
    └── main-wrapper
        ├── topbar
        └── page-content
```

### 6.2 Body

```css
body {
  margin: 0;
  background: #F8FAFC;
  color: #0F172A;
  font-family: Inter, system-ui, sans-serif;
}
```

### 6.3 Sidebar Width

- Expanded sidebar width: `270px`
- Collapsed sidebar width: `72px`
- Sidebar fixed left: `left: 0; top: 0; bottom: 0;`
- Sidebar border-right: `1px solid #E2E8F0`

### 6.4 Topbar Height

- Height: `64px`
- Background: `#FFFFFF`
- Border-bottom: `1px solid #E2E8F0`
- Position: sticky or fixed at the top of main area.

### 6.5 Main Content

- Left offset: sidebar width.
- Top offset: topbar height.
- Padding: `24px 28px 32px 28px`
- Gap between sections: `20px`
- Background: `#F8FAFC`

---

## 7. Sidebar Design

### 7.1 Sidebar Container

The sidebar is white, full-height, compact, and divided into navigation groups.

```css
.sidebar {
  width: 270px;
  height: 100vh;
  background: #FFFFFF;
  border-right: 1px solid #E2E8F0;
  padding: 14px 14px 16px 14px;
  display: flex;
  flex-direction: column;
}
```

### 7.2 Logo Area

Top logo area:

- Height: around `42px`
- Horizontal layout.
- Logo icon on the left.
- Text `Laundry POS` on the right.
- Icon is a blue square/rounded logo with laundry machine motif.

Logo icon:

- Size: `36px x 36px`
- Border radius: `8px`
- Background: blue gradient or primary blue.
- Icon color: white.

Logo text:

- Font size: `20px`
- Font weight: `700`
- Color: `#0F172A`

### 7.3 Outlet Selector Card

Located below the logo.

Visual style:

- Height: `54px`
- Border: `1px solid #93C5FD`
- Background: `#F8FBFF`
- Border radius: `10px`
- Padding: `8px 10px`
- Icon container: blue square `32px`
- Chevron on the right.

Content:

- Small label: `Outlet`
- Main text: `Central Surabaya`

Text rules:

- Label: `11px`, `500`, `#64748B`
- Outlet name: `13px`, `700`, `#0F172A`

### 7.4 Sidebar Navigation Groups

Group label style:

```css
.sidebar-group-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  color: #64748B;
  margin: 14px 8px 6px;
}
```

The sidebar groups in the reference:

#### Main Menu

- Dashboard
- Create Order
- Orders
- Customers

#### Operations

- Payments
- Laundry Status
- Pickup Management
- Delivery Management
- Order Timeline
- Notifications

#### Management

- Services
- Service Categories
- Price List
- Discounts / Promotions
- Outlets / Branches
- Staff / Users
- Roles & Permissions
- Customers Database

#### Reports

- Sales Report
- Payment Report
- Order Report
- Customer Report
- Staff Performance
- Service Performance
- Expense Report
- Export Reports

#### Finance

- Transactions
- Cashier Session
- Cash Flow
- Daily Closing
- Refunds

#### System

- WhatsApp Templates
- Invoice Settings
- Payment Gateway Settings
- Laundry Status Settings
- Business Profile
- General Settings

#### Account

- My Profile
- Logout

### 7.5 Sidebar Menu Item

Default item:

```css
.sidebar-item {
  height: 24px;
  padding: 0 10px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #334155;
}
```

Icon:

- Size: `14px`
- Stroke width: `1.8px`
- Color: `#64748B`

Active item:

```css
.sidebar-item.active {
  background: #DBEAFE;
  color: #2563EB;
  font-weight: 700;
}
```

Active icon:

- Color: `#2563EB`

Hover item:

```css
.sidebar-item:hover {
  background: #F1F5F9;
  color: #0F172A;
}
```

### 7.6 Collapse Menu Button

Positioned at the bottom of the sidebar.

Container:

- Height: `44px`
- Border: `1px solid #E2E8F0`
- Border radius: `12px`
- Background: `#FFFFFF`
- Padding: `8px 12px`
- Icon circle: `28px x 28px`
- Text: `Collapse Menu`

---

## 8. Topbar Design

### 8.1 Topbar Container

```css
.topbar {
  height: 64px;
  background: #FFFFFF;
  border-bottom: 1px solid #E2E8F0;
  padding: 0 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
```

Topbar content:

Left side:

- Hamburger menu icon.
- Search input.

Right side:

- Outlet selector button.
- Create Order primary button.
- Vertical divider.
- Notification bell with red badge.
- User avatar and profile dropdown.

### 8.2 Hamburger Button

- Size: `36px x 36px`
- No filled background.
- Icon size: `22px`
- Color: `#0F172A`
- Border radius: `8px`
- Hover background: `#F1F5F9`

### 8.3 Search Input

The search bar is wide and placed in the top-left content.

Dimensions:

- Width: around `680px`
- Height: `44px`
- Border radius: `12px`
- Border: `1px solid #E2E8F0`
- Background: `#FFFFFF`
- Padding left: `44px`
- Placeholder color: `#94A3B8`

Search icon:

- Size: `18px`
- Position: left `16px`
- Color: `#64748B`

Keyboard shortcut badge:

- Text: `Ctrl + K`
- Background: `#F1F5F9`
- Border: `1px solid #E2E8F0`
- Border radius: `6px`
- Font size: `11px`
- Color: `#64748B`

### 8.4 Outlet Selector Button

- Width: around `210px`
- Height: `44px`
- Border: `1px solid #E2E8F0`
- Border radius: `10px`
- Background: `#FFFFFF`
- Icon: storefront/building icon
- Text: `Central Surabaya`
- Chevron right

### 8.5 Create Order Button

Primary CTA.

```css
.create-order-button {
  height: 46px;
  padding: 0 22px;
  background: #2563EB;
  color: #FFFFFF;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 700;
  box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
}
```

Icon:

- Plus icon size: `18px`
- Gap: `10px`

Hover:

- Background: `#1D4ED8`

### 8.6 Notification Icon

- Wrapper size: `40px x 40px`
- Icon: bell, `20px`
- Badge: red circle at top-right
- Badge text: `8`
- Badge background: `#EF4444`
- Badge size: `18px`
- Badge font size: `10px`
- Badge text color: white

### 8.7 User Profile Area

User profile block:

- Avatar size: `40px x 40px`
- Avatar uses circular illustration.
- Text block on right.
- Name: `Admin Laundry`, `13px`, `700`, `#0F172A`
- Role: `Super Admin`, `12px`, `400`, `#64748B`
- Chevron icon at far right.

---

## 9. Main Content Layout

### 9.1 Page Header

Position under topbar.

Spacing:

- Top margin from topbar: `18px`
- Bottom margin before cards: `18px`

Title:

```txt
Admin Dashboard
```

Subtitle:

```txt
Monitor laundry operations, payments, orders, and staff activity in real time.
```

Title style:

- Size: `30px`
- Weight: `700`
- Color: `#0F172A`
- Line height: `38px`

Subtitle style:

- Size: `14px`
- Weight: `400`
- Color: `#64748B`
- Line height: `22px`

### 9.2 Section Spacing

Global vertical spacing:

- Between page header and stat cards: `16px`
- Between stat cards and chart/status row: `24px`
- Between chart/status row and quick actions: `18px`
- Between quick actions and recent orders: `18px`

---

## 10. Statistic Cards

### 10.1 Stat Card Grid

The reference uses 6 stat cards in a single row.

```css
.stats-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 20px;
}
```

Recommended sizes:

- Card height: `126px`
- Border radius: `14px`
- Padding: `20px`
- Background: `#FFFFFF`
- Border: `1px solid #E2E8F0`
- Shadow: subtle

```css
.stat-card {
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}
```

### 10.2 Stat Card Layout

The stat card has an icon container on the left and text on the right.

```txt
+------------------------------------------------+
| [icon]   Label                                 |
|          Main Value                            |
|          Trend / helper text                   |
+------------------------------------------------+
```

Icon container:

- Size: `58px x 58px`
- Border radius: `14px`
- Background based on card type.
- Icon size: `26px`

Text spacing:

- Gap between icon and content: `18px`
- Label margin bottom: `6px`
- Main value margin bottom: `6px`

### 10.3 Metric Content

Cards from left to right:

1. Today’s Revenue
   - Value: `Rp 8.450.000`
   - Trend: `↑ +8.2% from yesterday`
   - Icon color: green

2. Today’s Orders
   - Value: `56`
   - Trend: `↑ +12.5% from yesterday`
   - Icon color: blue

3. Active Orders
   - Value: `128`
   - Helper: `3 orders awaiting confirmation`
   - Icon color: purple

4. Ready for Pickup
   - Value: `42`
   - Trend: `↑ +5 from yesterday`
   - Icon color: cyan

5. Pending Payments
   - Value: `Rp 2.125.000`
   - Helper: `12 orders pending payment`
   - Icon color: orange

6. Completed Orders
   - Value: `74`
   - Trend: `↑ +9.3% from yesterday`
   - Icon color: green

### 10.4 Stat Card Text

Label:

- Size: `14px`
- Weight: `500`
- Color: `#64748B`

Value:

- Size: `26px`
- Weight: `700`
- Color: `#0F172A`

Positive trend:

- Arrow and percentage: green `#16A34A`
- Extra text: `#64748B`
- Size: `12px`

Warning helper:

- Number highlighted orange `#F59E0B`

---

## 11. Dashboard Mid Section

The middle section uses two main cards side-by-side:

```txt
+-----------------------------------------+----------------------------------+
| Revenue Overview                        | Laundry Status Overview           |
| Large chart                             | 2 x 4 status cards                |
+-----------------------------------------+----------------------------------+
```

Grid:

```css
.middle-grid {
  display: grid;
  grid-template-columns: 1.08fr 1fr;
  gap: 18px;
}
```

---

## 12. Revenue Overview Card

### 12.1 Card Container

- Background: `#FFFFFF`
- Border: `1px solid #E2E8F0`
- Border radius: `14px`
- Padding: `20px`
- Height: around `305px`
- Shadow: same as stat card

### 12.2 Header

Header layout:

- Left: title `Revenue Overview`
- Right: segmented filter and kebab menu

Title:

- Size: `18px`
- Weight: `700`
- Color: `#0F172A`

Segment control:

- Height: `34px`
- Border: `1px solid #E2E8F0`
- Border radius: `8px`
- Background: `#FFFFFF`
- Buttons: `Today`, `7 Days`, `30 Days`, `This Month`

Inactive segment:

- Padding: `0 16px`
- Text size: `12px`
- Weight: `500`
- Text color: `#64748B`
- Background: transparent

Active segment:

- Background: `#2563EB`
- Text color: `#FFFFFF`
- Border radius: `7px`
- Label: `30 Days`

Kebab icon:

- Size: `18px`
- Color: `#0F172A`
- Margin-left: `14px`

### 12.3 Chart Style

Chart type:

- Line chart with area fill.
- Blue line.
- Circular data point markers.
- Light blue gradient below line.

Chart styling:

- Line color: `#2563EB`
- Line width: `2px`
- Dot size: `6px`
- Dot fill: `#FFFFFF`
- Dot border: `#2563EB`
- Area fill: gradient from `rgba(37,99,235,0.16)` to transparent.

Grid lines:

- Horizontal dashed or dotted lines.
- Color: `#E2E8F0`
- Stroke width: `1px`

Y-axis labels:

- `Rp 0`, `Rp 2M`, `Rp 4M`, `Rp 6M`, `Rp 8M`, `Rp 10M`, `Rp 12M`
- Font size: `11px`
- Color: `#64748B`

X-axis labels:

- Date range: `21 Apr` to `10 May`
- Font size: `10px`
- Color: `#64748B`

### 12.4 Chart Footer

Bottom left:

- Small blue dot.
- Label: `Revenue (IDR)`

Bottom right:

- Text: `Total Revenue: Rp 156.750.000`

Footer style:

- Font size: `12px`
- Weight: `500`
- Text color: `#334155`

---

## 13. Laundry Status Overview Card

### 13.1 Card Container

- Background: `#FFFFFF`
- Border: `1px solid #E2E8F0`
- Border radius: `14px`
- Padding: `22px`
- Height: around `305px`

### 13.2 Header

Title:

```txt
Laundry Status Overview
```

Style:

- Size: `18px`
- Weight: `700`
- Color: `#0F172A`
- Margin bottom: `20px`

### 13.3 Status Card Grid

The status overview uses 8 status cards arranged in 4 columns and 2 rows.

```css
.status-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
```

Each card:

- Height: `88px`
- Border radius: `12px`
- Border: `1px solid` semantic soft border
- Background: very light semantic background
- Padding: `16px`
- Layout: icon on left, text on right/center

### 13.4 Status Cards

Status cards in order:

1. New Order
   - Value: `18`
   - Icon: plus in square
   - Blue background

2. Processing
   - Value: `36`
   - Icon: gear
   - Blue background

3. Washing
   - Value: `28`
   - Icon: water drop
   - Cyan background

4. Drying
   - Value: `22`
   - Icon: fan
   - Orange background

5. Ironing
   - Value: `16`
   - Icon: iron
   - Purple background

6. Ready for Pickup
   - Value: `42`
   - Icon: shopping bag
   - Cyan background

7. Completed
   - Value: `74`
   - Icon: check circle
   - Green background

8. Cancelled
   - Value: `8`
   - Icon: x circle
   - Red background

### 13.5 Status Card Text

Label:

- Size: `13px`
- Weight: `600`
- Color: `#0F172A`
- Center/right aligned according to available width.

Value:

- Size: `28px`
- Weight: `700`
- Color: `#0F172A`
- Line height: `34px`

Icon circle:

- Size: `42px x 42px`
- Border radius: `999px`
- Background: semantic soft background.
- Icon size: `22px`
- Icon color: semantic main color.

---

## 14. Quick Actions Section

### 14.1 Container

- Background: `#FFFFFF`
- Border: `1px solid #E2E8F0`
- Border radius: `14px`
- Padding: `20px 26px`
- Shadow: soft

Title:

- Text: `Quick Actions`
- Size: `18px`
- Weight: `700`
- Color: `#0F172A`
- Margin bottom: `14px`

### 14.2 Button Grid

The reference displays 6 equal-width action buttons in one row.

```css
.quick-action-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 18px;
}
```

Button style:

- Height: `46px`
- Background: `#FFFFFF`
- Border: `1px solid #CBD5E1`
- Border radius: `8px`
- Text size: `13px`
- Weight: `500`
- Text color: `#334155`
- Icon left.
- Icon color mostly blue except WhatsApp green.
- Hover background: `#F8FAFC`

Action buttons:

1. Create New Order
2. Search Order
3. Add Customer
4. Generate QRIS
5. Print Invoice
6. Send WhatsApp Reminder

Create New Order has stronger blue emphasis:

- Icon blue square/plus.
- Text color: `#2563EB`
- Font weight: `700`

WhatsApp button:

- Icon color: `#22C55E`

---

## 15. Recent Orders Table

### 15.1 Container

- Background: `#FFFFFF`
- Border: `1px solid #E2E8F0`
- Border radius: `14px`
- Padding: `0`
- Shadow: soft
- Overflow: hidden

### 15.2 Table Header Area

Top header inside card:

- Padding: `18px 26px 10px 26px`
- Display flex, justify-between, align-center.

Left title:

- Text: `Recent Orders`
- Size: `18px`
- Weight: `700`
- Color: `#0F172A`

Right button:

- Text: `View All Orders`
- Height: `34px`
- Padding: `0 14px`
- Border: `1px solid #E2E8F0`
- Border radius: `8px`
- Background: `#FFFFFF`
- Text size: `12px`
- Weight: `600`
- Color: `#334155`
- Arrow icon on right.

### 15.3 Table Structure

Columns:

1. Invoice
2. Customer
3. Service
4. Total
5. Payment Method
6. Payment Status
7. Laundry Status
8. Created At
9. Action

Table width: full.

Header row:

- Height: `34px`
- Border top: `1px solid #E2E8F0`
- Border bottom: `1px solid #E2E8F0`
- Background: `#FFFFFF`

Body row:

- Height: `43px`
- Border bottom: `1px solid #E2E8F0`
- Background: `#FFFFFF`
- Hover: `#F8FAFC`

Cell padding:

- Horizontal: `14px`
- Vertical: `8px`

Table header text:

- Size: `12px`
- Weight: `700`
- Color: `#0F172A`

Table body text:

- Size: `13px`
- Weight: `500`
- Color: `#0F172A`

Secondary row text:

- Phone number or quantity.
- Size: `12px`
- Weight: `400`
- Color: `#64748B`

### 15.4 Invoice Link

Invoice links use primary blue.

```css
.invoice-link {
  color: #2563EB;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
}
```

Hover:

- Underline or darker blue `#1D4ED8`.

### 15.5 Badge Style

Badge base:

```css
.badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 22px;
  padding: 0 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
  line-height: 1;
}
```

Badge variants:

#### Paid

- Background: `#DCFCE7`
- Text: `#16A34A`

#### Pending

- Background: `#FEF3C7`
- Text: `#F59E0B`

#### Failed

- Background: `#FEE2E2`
- Text: `#EF4444`

#### Ready for Pickup

- Background: `#CFFAFE`
- Text: `#0891B2`

#### Processing

- Background: `#DBEAFE`
- Text: `#2563EB`

#### Washing

- Background: `#CFFAFE`
- Text: `#0891B2`

#### Cancelled

- Background: `#FEE2E2`
- Text: `#DC2626`

#### Completed

- Background: `#DCFCE7`
- Text: `#15803D`

#### Ironing

- Background: `#EDE9FE`
- Text: `#7C3AED`

### 15.6 Action Button

Each row has a three-dot action button.

- Size: `28px x 28px`
- Border: `1px solid #E2E8F0`
- Border radius: `6px`
- Background: `#FFFFFF`
- Icon size: `16px`
- Icon color: `#334155`

Hover:

- Background: `#F8FAFC`

### 15.7 Dropdown Menu

The reference shows an open dropdown on the bottom-right row.

Dropdown style:

- Width: `170px`
- Background: `#FFFFFF`
- Border: `1px solid #E2E8F0`
- Border radius: `10px`
- Box shadow: `0 12px 28px rgba(15, 23, 42, 0.14)`
- Padding: `8px`
- Position: absolute, right aligned.

Dropdown item:

- Height: `36px`
- Padding: `0 10px`
- Border radius: `7px`
- Display flex, align center.
- Gap: `10px`
- Text size: `13px`
- Weight: `500`
- Color: `#334155`

Dropdown items:

1. View Detail
2. Update Status
3. Print Invoice
4. Send WhatsApp
5. Cancel Order

Danger item:

- Text color: `#EF4444`
- Icon color: `#EF4444`

Hover:

- Background: `#F8FAFC`

Danger hover:

- Background: `#FEF2F2`

### 15.8 Table Footer

Footer area:

- Height: `44px`
- Padding: `0 26px`
- Display flex, justify-between, align-center.

Left text:

- `Showing 1 to 6 of 6 orders`
- Size: `12px`
- Color: `#64748B`

Pagination:

- Previous and next buttons: `28px x 28px`
- Active page: blue square `28px x 28px`
- Active background: `#2563EB`
- Active text: white
- Border radius: `6px`

---

## 16. Icons

### 16.1 Icon Style

Use outline icons with consistent stroke width.

Recommended icon library:

- Lucide React
- Tabler Icons
- Heroicons Outline

Icon rules:

- Sidebar icons: `14px`, stroke `1.8px`
- Topbar icons: `18px - 22px`
- Stat card icons: `26px`
- Status icons: `22px`
- Quick action icons: `22px`
- Table action icons: `16px`

### 16.2 Icon Visual Rules

- Do not mix filled and outline styles too much.
- Primary action icons use blue.
- Semantic status icons follow status color.
- Sidebar icons are muted by default and primary blue when active.

---

## 17. Border Radius System

Use consistent rounded corners.

| Component | Radius |
|---|---:|
| Small icon button | `6px` |
| Sidebar active item | `6px` |
| Small badge | `6px` |
| Input | `10px - 12px` |
| Primary button | `10px` |
| Dropdown | `10px` |
| Quick action button | `8px` |
| Card | `14px` |
| Icon container | `14px` |
| Circular avatar/icon | `999px` |

---

## 18. Shadows

The design uses subtle shadows, never harsh shadows.

### 18.1 Card Shadow

```css
box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
```

### 18.2 Floating Dropdown Shadow

```css
box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
```

### 18.3 Primary Button Shadow

```css
box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
```

### 18.4 Avoid

Do not use:

- Heavy black shadows.
- Blurry oversized shadows.
- Neon glow effects.
- Dark background shadows.

---

## 19. Spacing System

Use a compact but readable spacing system.

Recommended spacing scale:

```txt
2px, 4px, 6px, 8px, 10px, 12px, 14px, 16px, 18px, 20px, 24px, 28px, 32px
```

### 19.1 Main Spacing Rules

- Sidebar padding: `14px`
- Topbar horizontal padding: `28px`
- Main content padding: `24px 28px 32px`
- Card padding: `20px - 24px`
- Grid gap: `18px - 20px`
- Table cell padding: `12px - 14px`
- Sidebar item gap: `8px`
- Button icon gap: `8px - 10px`

---

## 20. Forms and Inputs

Although the reference dashboard only shows the search input and selectors, all form elements in the system should follow the same style.

### 20.1 Input Base

```css
.input {
  height: 44px;
  border: 1px solid #CBD5E1;
  border-radius: 10px;
  background: #FFFFFF;
  padding: 0 14px;
  font-size: 14px;
  color: #0F172A;
}
```

Focus state:

```css
.input:focus {
  border-color: #2563EB;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
  outline: none;
}
```

Placeholder:

```css
.input::placeholder {
  color: #94A3B8;
}
```

### 20.2 Select / Dropdown Trigger

- Height: `44px`
- Border: `1px solid #E2E8F0`
- Radius: `10px`
- Background: white
- Text size: `14px`
- Text color: `#334155`
- Chevron icon on the right

---

## 21. Button System

### 21.1 Primary Button

Used for the main action: `Create Order`.

```css
.btn-primary {
  height: 46px;
  padding: 0 22px;
  background: #2563EB;
  color: #FFFFFF;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 700;
  border: none;
}
```

Hover:

```css
.btn-primary:hover {
  background: #1D4ED8;
}
```

### 21.2 Secondary Button

Used for quick actions and small controls.

```css
.btn-secondary {
  height: 40px;
  padding: 0 14px;
  background: #FFFFFF;
  color: #334155;
  border: 1px solid #CBD5E1;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
}
```

Hover:

```css
.btn-secondary:hover {
  background: #F8FAFC;
  border-color: #94A3B8;
}
```

### 21.3 Ghost Icon Button

Used for hamburger, notification, table actions.

```css
.btn-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: transparent;
  color: #334155;
}
```

Hover:

```css
.btn-icon:hover {
  background: #F1F5F9;
}
```

---

## 22. Component Layout Values

### 22.1 Approximate Reference Measurements

| Element | Approximate Size |
|---|---:|
| Sidebar width | `270px` |
| Topbar height | `64px` |
| Main content padding | `28px` |
| Search input width | `680px` |
| Search input height | `44px` |
| Stat card height | `126px` |
| Chart card height | `305px` |
| Status card height | `305px` |
| Quick action card height | `110px` |
| Recent orders card height | `370px` |
| Table row height | `43px` |
| Badge height | `22px` |

---

## 23. Responsive Rules

### 23.1 Desktop ≥ 1440px

- Full sidebar visible.
- Stat cards: 6 columns.
- Middle section: 2 columns.
- Quick actions: 6 columns.
- Table full width.

### 23.2 Laptop 1024px - 1439px

- Sidebar can remain visible but may be collapsible.
- Stat cards: 3 columns.
- Middle section: 1 column or 2 columns depending available width.
- Quick actions: 3 columns.
- Table horizontally scrollable.

### 23.3 Tablet 768px - 1023px

- Sidebar collapsed or drawer.
- Stat cards: 2 columns.
- Middle section: 1 column.
- Status grid: 2 columns.
- Quick actions: 2 columns.
- Table scrolls horizontally.

### 23.4 Mobile < 768px

- Sidebar becomes drawer.
- Topbar search becomes icon-triggered search.
- Create Order button remains visible.
- Stat cards: 1 column.
- Status cards: 1 or 2 columns.
- Recent orders should become card list instead of table.

---

## 24. Interaction States

### 24.1 Hover States

- Sidebar item: light gray background.
- Active sidebar item: blue background and blue text.
- Table row: light gray background.
- Secondary button: slightly darker border and light background.
- Primary button: darker blue.
- Dropdown item: light gray background.

### 24.2 Focus States

- Inputs and selects use blue focus ring.
- Buttons should show visible keyboard focus.

```css
:focus-visible {
  outline: 2px solid rgba(37, 99, 235, 0.6);
  outline-offset: 2px;
}
```

### 24.3 Active States

- Segmented control active button uses blue fill.
- Sidebar active item uses soft blue background.
- Pagination active page uses blue fill.

---

## 25. Data Formatting Rules

### 25.1 Currency

Use Indonesian Rupiah format:

```txt
Rp 8.450.000
Rp 75.000
Rp 156.750.000
```

Rules:

- Prefix with `Rp`.
- Use dot as thousands separator.
- No decimal values for IDR.

### 25.2 Dates

Use readable Indonesian date format:

```txt
10 May 2025, 09:30
```

### 25.3 Invoice

Use blue clickable invoice code:

```txt
INV-2025-05010
```

---

## 26. Content From Reference Screen

### 26.1 Dashboard Header

```txt
Admin Dashboard
Monitor laundry operations, payments, orders, and staff activity in real time.
```

### 26.2 Statistic Cards

```txt
Today's Revenue     Rp 8.450.000     +8.2% from yesterday
Today's Orders      56               +12.5% from yesterday
Active Orders       128              3 orders awaiting confirmation
Ready for Pickup    42               +5 from yesterday
Pending Payments    Rp 2.125.000     12 orders pending payment
Completed Orders    74               +9.3% from yesterday
```

### 26.3 Quick Actions

```txt
Create New Order
Search Order
Add Customer
Generate QRIS
Print Invoice
Send WhatsApp Reminder
```

### 26.4 Recent Orders Data Example

```txt
INV-2025-05010 | Budi Santoso | Cuci Setrika 5 Kg | Rp 75.000 | QRIS | Paid | Ready for Pickup | 10 May 2025, 09:30
INV-2025-05009 | Siti Nurhaliza | Cuci Kering 8 Kg | Rp 120.000 | Transfer Bank | Pending | Processing | 10 May 2025, 09:15
INV-2025-05008 | Andi Pratama | Cuci Setrika + Lipat 10 Kg | Rp 150.000 | Cash | Paid | Washing | 10 May 2025, 08:45
INV-2025-05007 | Dewi Lestari | Cuci Kering 6 Kg | Rp 90.000 | QRIS | Failed | Cancelled | 10 May 2025, 08:20
INV-2025-05006 | Hendra Wijaya | Cuci Bed Cover 1 Pcs | Rp 60.000 | Cash | Paid | Completed | 10 May 2025, 07:50
INV-2025-05005 | Rina Kartika | Cuci Setrika 4 Kg | Rp 60.000 | Transfer Bank | Pending | Ironing | 10 May 2025, 07:30
```

---

## 27. Implementation Notes for AI Agent

When generating the dashboard UI, follow these rules strictly:

1. Use a fixed left sidebar and topbar.
2. Keep the dashboard background light gray-blue `#F8FAFC`.
3. Use white cards with thin borders and soft shadows.
4. Use blue as the main action and active color.
5. Keep the sidebar menu complete and grouped exactly as described.
6. Use compact sidebar menu spacing to fit all menu groups.
7. Use 6 metric cards in one row on wide desktop.
8. Use a two-column middle section: revenue chart on the left, status overview on the right.
9. Use 8 laundry status cards in a 4x2 grid.
10. Use 6 quick action buttons in one row.
11. Use a readable recent orders table with status badges.
12. Use an open action dropdown on one row if creating a static high-fidelity mockup.
13. Keep all typography clean, consistent, and close to Inter.
14. Avoid gradients except subtle chart area fill or logo/icon accents.
15. Avoid dark mode for this design.
16. Avoid oversized icons and excessive decoration.

---

## 28. Suggested CSS Variables

```css
:root {
  --color-primary: #2563EB;
  --color-primary-hover: #1D4ED8;
  --color-primary-soft: #DBEAFE;
  --color-background: #F8FAFC;
  --color-surface: #FFFFFF;
  --color-border: #E2E8F0;
  --color-border-strong: #CBD5E1;
  --color-text: #0F172A;
  --color-text-secondary: #64748B;
  --color-text-muted: #94A3B8;

  --color-success: #16A34A;
  --color-success-soft: #DCFCE7;
  --color-warning: #F59E0B;
  --color-warning-soft: #FEF3C7;
  --color-danger: #EF4444;
  --color-danger-soft: #FEE2E2;
  --color-info: #0891B2;
  --color-info-soft: #CFFAFE;
  --color-purple: #8B5CF6;
  --color-purple-soft: #EDE9FE;

  --radius-sm: 6px;
  --radius-md: 8px;
  --radius-lg: 10px;
  --radius-xl: 14px;
  --radius-full: 999px;

  --shadow-card: 0 4px 12px rgba(15, 23, 42, 0.06);
  --shadow-dropdown: 0 12px 28px rgba(15, 23, 42, 0.14);
  --shadow-primary: 0 4px 10px rgba(37, 99, 235, 0.25);

  --sidebar-width: 270px;
  --topbar-height: 64px;
  --content-padding: 28px;
}
```

---

## 29. Tailwind Design Tokens

If using Tailwind CSS, use these approximate utility patterns:

```txt
App background: bg-slate-50
Surface/card: bg-white
Border: border border-slate-200
Main text: text-slate-900
Secondary text: text-slate-500
Primary: bg-blue-600 text-white
Primary hover: hover:bg-blue-700
Soft blue: bg-blue-50
Success: bg-green-100 text-green-700
Warning: bg-amber-100 text-amber-600
Danger: bg-red-100 text-red-600
Info: bg-cyan-100 text-cyan-700
Purple: bg-violet-100 text-violet-700
Card radius: rounded-[14px]
Input radius: rounded-xl
Small radius: rounded-md
Card shadow: shadow-[0_4px_12px_rgba(15,23,42,0.06)]
Dropdown shadow: shadow-[0_12px_28px_rgba(15,23,42,0.14)]
```

---

## 30. Final Design Summary

The final dashboard must visually match the reference image:

- White fixed sidebar with full menu groups.
- Topbar with search, outlet selector, create order button, notification, and profile.
- Light dashboard background.
- Large `Admin Dashboard` title and subtitle.
- Six statistic cards in a single row.
- Revenue chart card on the left.
- Laundry status overview card on the right with 8 colored status tiles.
- Quick actions card with six bordered buttons.
- Recent orders table with badges, invoice links, action dropdown, and pagination.
- Consistent spacing, blue accents, soft borders, rounded corners, and subtle shadows.

This design system should be reused for all admin and staff pages in the Laundry POS application so both roles feel visually consistent while access control determines which menus and features are available.
