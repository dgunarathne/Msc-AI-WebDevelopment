# Generator Fuel Management App - Figma Design Specifications

## Project Setup
- **File Name**: "Generator Fuel Management App – MAD Project"
- **Canvas Size**: Mobile (390x844) for all screens

---

## 🟢 Screen 1 – Runtime & Fuel (Tab Screen)

### Layout Structure
```
┌─────────────────────────┐
│ AppBar: "Generator Tracker" │
├─────────────────────────┤
│ [Runtime] [Fuel] (Tabs) │
├─────────────────────────┤
│                         │
│   Tab Content Area      │
│                         │
│                         │
│                         │
└─────────────────────────┘
```

### Components

#### AppBar
- Height: 56px
- Background: Primary color (e.g., #1976D2)
- Text: "Generator Tracker" (white, 18px, center)
- Info icon on the right (links to Screen 5)

#### Tabs
- Height: 48px
- Two tabs: "Runtime" and "Fuel"
- Active tab: Primary color, inactive: gray

#### Runtime Tab Content
- **Dropdown**: Select Generator (full width, 48px height)
- **Date Picker**: Date selection (full width, 48px height)
- **Input Field**: Hours (TextField, numeric keyboard)
- **Button**: Save (primary color, full width)

#### Fuel Tab Content
- **Dropdown**: Generator selection (full width, 48px height)
- **Date Picker**: Date selection (full width, 48px height)
- **Input Field**: Liters Added (numeric)
- **Input Field**: Diesel Price (numeric, decimal)
- **Button**: Save (primary color, full width)

#### Popup Modal
- Title: "Are you sure?"
- Buttons: "Yes" (primary) and "No" (secondary)
- Background overlay: semi-transparent black

---

## 🟢 Screen 2 – Generator List

### Layout Structure
```
┌─────────────────────────┐
│ AppBar: "Generators"    │
├─────────────────────────┤
│ ┌─────────────────────┐ │
│ │ Generator Name + Code │
│ │            Fuel Balance │
│ └─────────────────────┘ │
│ ┌─────────────────────┐ │
│ │ Generator Name + Code │
│ │            Fuel Balance │
│ └─────────────────────┘ │
│                         │
│      (List continues)   │
│                         │
│         [+] FAB         │
└─────────────────────────┘
```

### Components
- **AppBar**: "Generators" with back button
- **List Items**: 
  - Height: 72px each
  - Left: Generator Name + Code
  - Right: Fuel Balance (aligned right)
  - Divider between items
- **FAB**: Floating Action Button (+) at bottom right
- **Swipe Action**: Left swipe reveals delete option with confirmation dialog

---

## 🟢 Screen 3 – Add Generator

### Layout Structure
```
┌─────────────────────────┐
│ AppBar: "Add Generator" │
├─────────────────────────┤
│                         │
│ Name / Location         │
│ [Text Field]            │
│                         │
│ Generator Code          │
│ [Text Field]            │
│                         │
│ Tank Capacity (Liters)  │
│ [Number Field]          │
│                         │
│ Consumption Rate (L/hr) │
│ [Number Field]          │
│                         │
│    [SAVE BUTTON]        │
│                         │
└─────────────────────────┘
```

### Components
- **AppBar**: "Add Generator" with back button
- **Input Fields**:
  - Name / Location (text)
  - Generator Code (text)
  - Tank Capacity (number)
  - Consumption Rate (number)
- **Save Button**: Primary color, full width

---

## 🟢 Screen 4 – Generator Details

### Layout Structure
```
┌─────────────────────────┐
│ AppBar: "Generator Details" │
├─────────────────────────┤
│                         │
│ Name + Code             │
│                         │
│ Tank Capacity: 1000L    │
│ Consumption Rate: 5L/hr │
│ Total Runtime: 120h     │
│ Fuel Added: 500L        │
│                         │
│ ┌─────────────────────┐ │
│ │ Remaining Fuel       │ │
│ │ 100L                 │ │
│ └─────────────────────┘ │
│                         │
│ Formula:                │
│ Remaining Fuel =        │
│ Fuel Added -           │
│ (Runtime × Consumption) │
│                         │
└─────────────────────────┘
```

### Components
- **AppBar**: "Generator Details" with back button
- **Display Fields**: All generator information
- **Remaining Fuel**: Highlighted box
- **Formula**: Displayed at bottom for reference

---

## 🟢 Screen 5 – App Info

### Layout Structure
```
┌─────────────────────────┐
│ AppBar: "App Info"      │
├─────────────────────────┤
│                         │
│     [APP ICON]          │
│                         │
│ Generator Fuel          │
│ Management App          │
│                         │
│ Version: 1.0.0          │
│ Developer: Your Name    │
│ Build Number: 1         │
│ Device: iPhone 14      │
│                         │
│                         │
└─────────────────────────┘
```

### Components
- **AppBar**: "App Info" with back button
- **App Icon**: Centered
- **Information List**: Version, Developer, Build, Device info

---

## 🔗 Screen Connections (Figma Prototyping)

### Navigation Flow:
1. **Screen 2 → FAB → Screen 3**: Tap FAB to add new generator
2. **Screen 2 → Tap Generator → Screen 4**: Tap generator item to view details
3. **Screen 1 → Info Icon → Screen 5**: Tap info icon in app bar
4. **Screen 1 Save → Popup → Back to Screen 1**: Save action shows confirmation
5. **Screen 3 Save → Back to Screen 2**: Save returns to generator list

### Interaction Types:
- Use "On Tap → Navigate To" for all navigation
- Add smooth transitions (e.g., "Move In" for forward navigation)
- Use "Dissolve" for modal popups

---

## Design System Recommendations

### Colors:
- **Primary**: #1976D2 (Blue)
- **Primary Dark**: #1565C0
- **Accent**: #FF5722 (Orange)
- **Background**: #FFFFFF
- **Surface**: #F5F5F5
- **Text Primary**: #212121
- **Text Secondary**: #757575

### Typography:
- **Headline**: 24px, Medium
- **Title**: 20px, Medium
- **Body**: 16px, Regular
- **Caption**: 14px, Regular

### Components:
- **Buttons**: 48px height, 8px border radius
- **Text Fields**: 48px height, 8px border radius
- **Cards**: 8px border radius, 2px elevation
- **FAB**: 56px diameter, circular

---

## Next Steps

1. **Create Figma file** with specified name
2. **Set up frames** for each screen (390x844)
3. **Create components** following the specifications
4. **Set up prototyping** connections
5. **Test interactions** in prototype mode

Would you like me to provide more detailed specifications for any specific screen or component?
