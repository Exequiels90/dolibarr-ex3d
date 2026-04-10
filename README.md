# Ex3D Production Management System

A comprehensive production management and cost-tracking system for Ex3D 3D printing micro-business, built with Laravel 11, Filament PHP v3, and optimized for deployment on Render.com with Supabase (PostgreSQL).

## Features

### Core Business Logic
- **Filament Management**: Track filament types, colors, costs per kg, and spool weights
- **Additional Supplies**: Manage inventory of supplementary items (inserts, rings, etc.)
- **Product Templates**: Create manufacturing templates with 3D printing specifications
- **Automated Cost Calculation**: Real-time production cost calculation including:
  - Filament cost (grams × cost/kg)
  - Machine operation cost (hours × hourly rate)
  - Additional supplies cost
  - Post-processing labor costs
  - Safety margin for print failures
- **Work Queue Management**: Order tracking with customer information and status management
- **Maintenance Tracking**: Monitor printer usage and schedule maintenance alerts

### User Interface
- **Complete Spanish Localization**: All UI elements, forms, and labels are in Spanish for daily use in Argentina
- **Modern Dark Mode UI**: Minimalist design optimized for production environments
- **Filament PHP v3 Panels**: Professional admin interface with intuitive Spanish navigation
- **Responsive Design**: Works on desktop and mobile devices
- **Real-time Updates**: Live cost calculations and status updates

### Dashboard & Analytics
- **Monthly Net Profit Widget**: Track profitability with month-over-month comparisons
- **Active Orders Widget**: Monitor order status (Pending, In Printer, Finished, Delivered)
- **Total Filament Needed Widget**: Calculate filament requirements for active orders
- **Revenue vs Real Costs Chart**: 30-day visualization of revenue, costs, and profit

### User Interface
- **Modern Dark Mode UI**: Minimalist design optimized for production environments
- **Filament PHP v3 Panels**: Professional admin interface with intuitive navigation
- **Responsive Design**: Works on desktop and mobile devices
- **Real-time Updates**: Live cost calculations and status updates

## Technical Stack

- **Backend**: Laravel 11 (PHP 8.2)
- **Frontend**: Filament PHP v3 (TailwindCSS)
- **Database**: PostgreSQL (Supabase)
- **Deployment**: Docker + Render.com
- **Authentication**: Laravel Filament Auth

## Database Models

### Filament
- `brand_type`: Brand and material type (e.g., PLA, PETG, ABS)
- `color`: Filament color
- `cost_per_kg`: Cost per kilogram
- `spool_weight_g`: Weight of filament spool in grams

### AdditionalSupply
- `item_name`: Supply item name
- `unit_cost`: Cost per unit

### Product
- `name`: Product name
- `description`: Product description
- `filament_id`: Foreign key to Filament
- `total_grams`: Total filament weight required
- `printing_time_hours`: Printing time from Bambu Studio
- `post_processing_cost`: Manual labor/finishing costs
- `safety_margin_percentage`: Safety margin percentage

### WorkQueue
- `customer_name`: Customer name
- `customer_phone`: Customer phone number
- `product_id`: Foreign key to Product
- `agreed_price`: Agreed selling price
- `delivery_date`: Expected delivery date
- `status`: Order status (pending, in_printer, finished, delivered)

### Maintenance
- `machine_name`: Printer name (e.g., Bambu Lab A1)
- `total_print_hours`: Cumulative print hours
- `last_maintenance_hours`: Hours at last maintenance
- `maintenance_interval_hours`: Maintenance interval (default: 100 hours)

## Setup Instructions

### Prerequisites
- PHP 8.2+
- Composer
- PostgreSQL database
- Docker (for deployment)
- Render.com account (for production deployment)

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd ex3d
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=ex3d
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Set environment variables**
   ```env
   HOURLY_MACHINE_RATE=5.00
   SAFETY_MARGIN_PERCENTAGE=10
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Create Filament admin user**
   ```bash
   php artisan make:filament-user
   ```

8. **Link storage**
   ```bash
   php artisan storage:link
   ```

9. **Start development server**
   ```bash
   php artisan serve
   ```

10. **Access the application**
    - URL: `http://localhost:8000`
    - Admin Panel: `http://localhost:8000/admin`

### Production Deployment (Render.com)

1. **Create Render.com account** and set up a new Web Service

2. **Configure environment variables** on Render:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=<generate-laravel-key>
   DB_CONNECTION=pgsql
   DB_HOST=<supabase-host>
   DB_PORT=5432
   DB_DATABASE=<supabase-database>
   DB_USERNAME=<supabase-username>
   DB_PASSWORD=<supabase-password>
   HOURLY_MACHINE_RATE=5.00
   SAFETY_MARGIN_PERCENTAGE=10
   ```

3. **Set build command**:
   ```
   chmod +x build.sh && ./build.sh
   ```

4. **Set start command**:
   ```
   php-fpm -D && nginx -g "daemon off;"
   ```

5. **Configure Docker deployment**:
   - Use the provided `Dockerfile`
   - Set health check to `/health`
   - Configure auto-scaling as needed

## Implementation Checklist

### Core Features Implemented
- [x] Laravel 11 project structure
- [x] Database models with proper relationships
- [x] Database migrations for all models
- [x] Filament PHP v3 admin panel
- [x] Automated cost calculation logic
- [x] Safety margin calculations
- [x] Work queue management system
- [x] Maintenance tracking with alerts
- [x] Dashboard widgets
- [x] Revenue vs Costs chart
- [x] Dark mode UI implementation
- [x] HTTPS force scheme for production
- [x] Docker configuration for deployment
- [x] Build script for Render.com

### Production-Ready Enhancements
- [x] **Precise Sales Tracking**: Added `delivered_at` timestamp field to WorkQueue model
- [x] **Model Observer**: Automatic timestamp setting when status changes to 'delivered'
- [x] **Reactive Cost Calculation**: Real-time cost updates in ProductResource forms
- [x] **Supplies Cost Integration**: Verified proper iteration through AdditionalSupplies
- [x] **Enhanced Dashboard**: RevenueVsCostsChart uses `delivered_at` for real sales performance
- [x] **Health Check Endpoint**: `/health` route returning 200 OK status
- [x] **Production Optimization**: Build script includes config:cache and route:cache
- [x] **Localization**: Set timezone to America/Argentina/Buenos_Aires and locale to 'es'

### Spanish UI Localization
- [x] **Complete Interface Translation**: All Filament resources, forms, and labels translated to Spanish
- [x] **Navigation Groups**: 'Gestión de Producción', 'Pedidos y Cola', 'Sistema'
- [x] **Resource Labels**: 'Filamentos', 'Insumos Adicionales', 'Productos', 'Cola de Trabajo', 'Mantenimiento'
- [x] **Status Labels**: 'Pendiente', 'En Impresora', 'Terminado', 'Entregado'
- [x] **Dashboard Widgets**: 'Ganancia Neta Mensual', 'Pedidos Activos', 'Filamento Necesario', 'Ingresos vs Costos'
- [x] **Form Fields**: All input labels, descriptions, and placeholders in Spanish
- [x] **Table Columns**: All table headers and content labels in Spanish
- [x] **Internal Code**: Variable names and database columns remain in English for stability

### Filament Resources Created
- [x] FilamentResource (filament management)
- [x] AdditionalSupplyResource (supplies management)
- [x] ProductResource (product templates with repeaters)
- [x] WorkQueueResource (order management)
- [x] MaintenanceResource (maintenance tracking)

### Dashboard Widgets Implemented
- [x] MonthlyNetProfitWidget
- [x] ActiveOrdersWidget
- [x] TotalFilamentNeededWidget
- [x] RevenueVsCostsChart

### Deployment Configuration
- [x] Dockerfile optimized for Render.com
- [x] Nginx configuration
- [x] PHP-FPM configuration
- [x] Supervisor configuration
- [x] Build script for Render.com
- [x] Environment configuration
- [x] HTTPS force scheme

### Security & Performance
- [x] Production environment optimizations
- [x] Database security
- [x] File upload restrictions (disabled as requested)
- [x] No local storage usage
- [x] Optimized for PostgreSQL
- [x] Memory-efficient Docker configuration

## Usage Guide

### Managing Filaments
1. Navigate to Admin Panel > Production Management > Filaments
2. Add new filament types with brand, color, cost per kg, and spool weight
3. Filaments are used in product templates for cost calculations

### Creating Product Templates
1. Go to Admin Panel > Production Management > Products
2. Create new product with:
   - Basic information (name, description)
   - 3D printing specs (filament type, grams, printing time)
   - Additional supplies (using repeater for quantities)
   - Cost configuration (post-processing, safety margin)
3. System automatically calculates total production cost

### Managing Orders
1. Navigate to Admin Panel > Orders & Queue > Work Queue
2. Add new orders with customer information
3. Select product template (cost is automatically calculated)
4. Set agreed price and delivery date
5. Track order status through production pipeline
6. Net profit is calculated automatically

### Maintenance Tracking
1. Go to Admin Panel > System > Maintenance
2. Add your 3D printer (e.g., Bambu Lab A1)
3. Track total print hours
4. System alerts when maintenance is due (every 100 hours)
5. Use quick actions to add hours or perform maintenance

### Dashboard Monitoring
- Check monthly profitability trends
- Monitor active orders by status
- Track filament requirements for production
- Analyze revenue vs costs over 30 days

## Environment Variables

### Required Variables
- `APP_KEY`: Laravel application key
- `DB_*`: Database connection details
- `HOURLY_MACHINE_RATE`: Cost per hour of machine operation
- `SAFETY_MARGIN_PERCENTAGE`: Default safety margin percentage

### Optional Variables
- `FILAMENT_ADMIN_PATH`: Custom admin panel path (default: admin)
- `APP_URL`: Application URL (auto-detected on Render)

## Support

For technical support or questions about the Ex3D Production Management System, please refer to the documentation or contact the development team.

## License

This project is licensed under the MIT License.
