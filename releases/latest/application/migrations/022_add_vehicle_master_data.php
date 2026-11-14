<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_vehicle_master_data extends CI_Migration {

    public function up() {
        // Makes
        if (!$this->db->table_exists('db_vehicle_makes')) {
            $this->dbforge->add_field([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'store_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'default' => 0],
                'name' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => FALSE],
                'status' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => TRUE],
                'updated_at' => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('db_vehicle_makes', TRUE);
        }

        // Models
        if (!$this->db->table_exists('db_vehicle_models')) {
            $this->dbforge->add_field([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'store_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'default' => 0],
                'make_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE],
                'name' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => FALSE],
                'status' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => TRUE],
                'updated_at' => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('db_vehicle_models', TRUE);
        }

        // Attribute options (body_type, color, fuel_type, transmission, condition, drivetrain, trim, etc.)
        if (!$this->db->table_exists('db_vehicle_attribute_options')) {
            $this->dbforge->add_field([
                'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE],
                'store_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'default' => 0],
                'attribute_type' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => FALSE],
                'attribute_value' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => FALSE],
                'sort_order' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'default' => 0],
                'status' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at' => ['type' => 'DATETIME', 'null' => TRUE],
                'updated_at' => ['type' => 'DATETIME', 'null' => TRUE],
            ]);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('db_vehicle_attribute_options', TRUE);
        }

        $this->seed_defaults();
    }

    private function seed_defaults() {
        $default_makes = [
            'Toyota' => ['Corolla', 'Camry', 'RAV4', 'Hilux', 'Prado', 'Land Cruiser', 'Yaris', 'Highlander', 'Avalon', 'Sienna'],
            'Honda' => ['Civic', 'Accord', 'CR-V', 'HR-V', 'Pilot', 'Odyssey', 'Jazz', 'City', 'Ridgeline', 'Insight'],
            'Ford' => ['Focus', 'Fusion', 'Mustang', 'F-150', 'Explorer', 'Escape', 'Edge', 'Ranger', 'Bronco', 'Expedition'],
            'BMW' => ['3 Series', '5 Series', '7 Series', 'X3', 'X5', 'X6', 'X7', 'M3', 'M5', 'Z4'],
            'Mercedes-Benz' => ['A-Class', 'C-Class', 'E-Class', 'S-Class', 'GLA', 'GLC', 'GLE', 'GLS', 'CLA', 'G-Class'],
            'Nissan' => ['Altima', 'Sentra', 'Maxima', 'Rogue', 'Pathfinder', 'Murano', 'Frontier', 'Titan', 'Armada', 'Juke'],
            'Hyundai' => ['Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Palisade', 'Kona', 'Creta', 'Accent', 'Venue', 'Ioniq'],
            'Volkswagen' => ['Golf', 'Passat', 'Jetta', 'Tiguan', 'Atlas', 'Arteon', 'ID.4', 'Beetle', 'Touareg', 'Amarok'],
            'Kia' => ['Rio', 'Cerato', 'Optima', 'Sorento', 'Sportage', 'Telluride', 'Seltos', 'Carnival', 'Stinger', 'EV6'],
            'Mazda' => ['Mazda2', 'Mazda3', 'Mazda6', 'CX-3', 'CX-5', 'CX-9', 'MX-5', 'CX-30', 'CX-50', 'BT-50'],
            'Lexus' => ['IS', 'ES', 'GS', 'LS', 'RX', 'NX', 'UX', 'GX', 'LX', 'LC'],
            'Audi' => ['A3', 'A4', 'A6', 'A8', 'Q3', 'Q5', 'Q7', 'Q8', 'TT', 'e-tron'],
            'Chevrolet' => ['Cruze', 'Malibu', 'Camaro', 'Silverado', 'Equinox', 'Traverse', 'Tahoe', 'Suburban', 'Trailblazer', 'Blazer'],
            'Tesla' => ['Model S', 'Model 3', 'Model X', 'Model Y', 'Cybertruck', 'Roadster'],
            'Peugeot' => ['208', '308', '508', '3008', '5008', 'Rifter', '2008', 'Boxer', 'Traveller', 'Landtrek'],
            'Subaru' => ['Impreza', 'Legacy', 'Outback', 'Forester', 'Crosstrek', 'Ascent', 'BRZ', 'WRX', 'Solterra', 'XV'],
            'Land Rover' => ['Defender', 'Discovery', 'Discovery Sport', 'Range Rover', 'Range Rover Sport', 'Range Rover Velar', 'Range Rover Evoque', 'Freelander'],
            'Jeep' => ['Wrangler', 'Grand Cherokee', 'Cherokee', 'Compass', 'Renegade', 'Gladiator', 'Wagoneer', 'Grand Wagoneer'],
        ];

        foreach ($default_makes as $make => $models) {
            $this->db->insert('db_vehicle_makes', ['store_id' => 0, 'name' => $make, 'status' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
            $make_id = $this->db->insert_id();
            foreach ($models as $model) {
                $this->db->insert('db_vehicle_models', ['store_id' => 0, 'make_id' => $make_id, 'name' => $model, 'status' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
            }
        }

        $default_attributes = [
            ['attribute_type' => 'body_type', 'attribute_value' => 'Sedan', 'sort_order' => 1],
            ['attribute_type' => 'body_type', 'attribute_value' => 'SUV', 'sort_order' => 2],
            ['attribute_type' => 'body_type', 'attribute_value' => 'Truck', 'sort_order' => 3],
            ['attribute_type' => 'body_type', 'attribute_value' => 'Coupe', 'sort_order' => 4],
            ['attribute_type' => 'body_type', 'attribute_value' => 'Hatchback', 'sort_order' => 5],
            ['attribute_type' => 'body_type', 'attribute_value' => 'Wagon', 'sort_order' => 6],
            ['attribute_type' => 'body_type', 'attribute_value' => 'Van', 'sort_order' => 7],
            ['attribute_type' => 'body_type', 'attribute_value' => 'Bus', 'sort_order' => 8],
            ['attribute_type' => 'body_type', 'attribute_value' => 'Convertible', 'sort_order' => 9],

            ['attribute_type' => 'fuel_type', 'attribute_value' => 'Petrol', 'sort_order' => 1],
            ['attribute_type' => 'fuel_type', 'attribute_value' => 'Diesel', 'sort_order' => 2],
            ['attribute_type' => 'fuel_type', 'attribute_value' => 'Hybrid', 'sort_order' => 3],
            ['attribute_type' => 'fuel_type', 'attribute_value' => 'Electric', 'sort_order' => 4],

            ['attribute_type' => 'transmission', 'attribute_value' => 'Automatic', 'sort_order' => 1],
            ['attribute_type' => 'transmission', 'attribute_value' => 'Manual', 'sort_order' => 2],
            ['attribute_type' => 'transmission', 'attribute_value' => 'CVT', 'sort_order' => 3],

            ['attribute_type' => 'drivetrain', 'attribute_value' => 'FWD', 'sort_order' => 1],
            ['attribute_type' => 'drivetrain', 'attribute_value' => 'RWD', 'sort_order' => 2],
            ['attribute_type' => 'drivetrain', 'attribute_value' => 'AWD', 'sort_order' => 3],
            ['attribute_type' => 'drivetrain', 'attribute_value' => '4WD', 'sort_order' => 4],

            ['attribute_type' => 'condition', 'attribute_value' => 'New', 'sort_order' => 1],
            ['attribute_type' => 'condition', 'attribute_value' => 'Foreign Used', 'sort_order' => 2],
            ['attribute_type' => 'condition', 'attribute_value' => 'Locally Used', 'sort_order' => 3],
            ['attribute_type' => 'condition', 'attribute_value' => 'Accident Free', 'sort_order' => 4],
            ['attribute_type' => 'condition', 'attribute_value' => 'Certified Pre-Owned', 'sort_order' => 5],

            ['attribute_type' => 'color', 'attribute_value' => 'White', 'sort_order' => 1],
            ['attribute_type' => 'color', 'attribute_value' => 'Black', 'sort_order' => 2],
            ['attribute_type' => 'color', 'attribute_value' => 'Silver', 'sort_order' => 3],
            ['attribute_type' => 'color', 'attribute_value' => 'Grey', 'sort_order' => 4],
            ['attribute_type' => 'color', 'attribute_value' => 'Blue', 'sort_order' => 5],
            ['attribute_type' => 'color', 'attribute_value' => 'Red', 'sort_order' => 6],
            ['attribute_type' => 'color', 'attribute_value' => 'Green', 'sort_order' => 7],
            ['attribute_type' => 'color', 'attribute_value' => 'Gold', 'sort_order' => 8],
            ['attribute_type' => 'color', 'attribute_value' => 'Brown', 'sort_order' => 9],
            ['attribute_type' => 'color', 'attribute_value' => 'Beige', 'sort_order' => 10],
            ['attribute_type' => 'color', 'attribute_value' => 'Yellow', 'sort_order' => 11],
            ['attribute_type' => 'color', 'attribute_value' => 'Orange', 'sort_order' => 12],
            ['attribute_type' => 'color', 'attribute_value' => 'Purple', 'sort_order' => 13],
            ['attribute_type' => 'color', 'attribute_value' => 'Maroon', 'sort_order' => 14],
        ];

        foreach ($default_attributes as $attr) {
            $this->db->insert('db_vehicle_attribute_options', array_merge(['store_id' => 0, 'status' => 1, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')], $attr));
        }
    }

    public function down() {
        $this->dbforge->drop_table('db_vehicle_attribute_options', TRUE);
        $this->dbforge->drop_table('db_vehicle_models', TRUE);
        $this->dbforge->drop_table('db_vehicle_makes', TRUE);
    }
}
