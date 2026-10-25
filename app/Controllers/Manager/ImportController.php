<?php
namespace App\Controllers\Manager;

use App\Controllers\BaseController;
use App\Libraries\FileTableReader;

/**
 * Admin import: menu items & customers from CSV/XLSX with an
 * interactive column-mapping step (file header  ->  DB field),
 * including "custom value" mapping for fields that are missing
 * in the uploaded file (applied to every row).
 */
class ImportController extends BaseController
{
    private const TYPES = ['menu', 'customer'];

    // ── DB-field definitions per type ─────────────────────────
    private function fieldsFor(string $type, int $rid): array
    {
        $db = \Config\Database::connect();

        if ($type === 'menu') {
            $cats = [];
            foreach ($db->table('menu_categories')
                ->where('restaurant_id', $rid)
                ->orderBy('name', 'ASC')->get()->getResultArray() as $c) {
                $cats[] = ['id' => (int)$c['id'], 'label' => $c['name']];
            }
            return [
                ['key' => 'name',         'label' => 'Item name',        'required' => true,  'widget' => 'text',
                 'aliases' => ['name','item_name','item name','product','product_name','product name','title','menu item','item']],
                ['key' => 'base_price',   'label' => 'Price',            'required' => true,  'widget' => 'number',
                 'aliases' => ['price','base_price','mrp','rate','cost','amount','selling price','sale price','unit price']],
                ['key' => 'category',     'label' => 'Category',         'required' => false, 'widget' => 'entity', 'options' => $cats,
                 'aliases' => ['category','category_id','cat','section','group','category name']],
                ['key' => 'tax_percent',  'label' => 'Tax %',            'required' => false, 'widget' => 'number', 'default' => '12',
                 'aliases' => ['tax','tax_percent','gst','tax %','tax%','vat','tax rate']],
                ['key' => 'item_type',    'label' => 'Veg / Non-veg',    'required' => false, 'widget' => 'enum',
                 'options' => [
                     ['id' => 'veg', 'label' => 'Veg'], ['id' => 'non_veg', 'label' => 'Non-veg'],
                     ['id' => 'egg', 'label' => 'Egg'], ['id' => 'vegan', 'label' => 'Vegan'],
                 ],
                 'aliases' => ['item_type','veg','veg type','veg/nonveg','non_veg','dietary']],
                ['key' => 'food_type',    'label' => 'Menu section type','required' => false, 'widget' => 'enum',
                 'options' => [
                     ['id' => 'food', 'label' => 'Food'], ['id' => 'beverage', 'label' => 'Beverage'],
                     ['id' => 'dessert', 'label' => 'Dessert'], ['id' => 'combo', 'label' => 'Combo'],
                     ['id' => 'addon', 'label' => 'Add-on'], ['id' => 'other', 'label' => 'Other'],
                 ],
                 'aliases' => ['food_type','food type','serve','serving type','kind']],
                ['key' => 'description',  'label' => 'Description',      'required' => false, 'widget' => 'text',
                 'aliases' => ['description','desc','details','about','note']],
                ['key' => 'sku',          'label' => 'SKU / code',       'required' => false, 'widget' => 'text',
                 'aliases' => ['sku','code','item code','product code','short_code','barcode']],
            ];
        }

        // customers
        return [
            ['key' => 'name',         'label' => 'Full name',        'required' => true,  'widget' => 'text',
             'aliases' => ['name','customer_name','customer name','full_name','full name','cname']],
            ['key' => 'phone',        'label' => 'Mobile / phone',   'required' => true,  'widget' => 'text',
             'aliases' => ['phone','mobile','mobile_no','mobile no','phone_number','phone number','contact','contact_no','number','msisdn']],
            ['key' => 'email',        'label' => 'Email',            'required' => false, 'widget' => 'text',
             'aliases' => ['email','email_id','email id','mail','e-mail']],
            ['key' => 'address',      'label' => 'Address',          'required' => false, 'widget' => 'text',
             'aliases' => ['address','street','address1','location']],
            ['key' => 'city',         'label' => 'City',             'required' => false, 'widget' => 'text',
             'aliases' => ['city','town','district']],
            ['key' => 'pincode',      'label' => 'Pincode',          'required' => false, 'widget' => 'text',
             'aliases' => ['pincode','pin','pin_code','zip','zipcode','postal code','postcode']],
            ['key' => 'gender',       'label' => 'Gender',           'required' => false, 'widget' => 'enum',
             'options' => [['id'=>'male','label'=>'Male'],['id'=>'female','label'=>'Female'],['id'=>'other','label'=>'Other']],
             'aliases' => ['gender','sex']],
            ['key' => 'dob',          'label' => 'Date of birth',    'required' => false, 'widget' => 'date',
             'aliases' => ['dob','date_of_birth','birth date','birthday','date of birth']],
            ['key' => 'anniversary',  'label' => 'Anniversary',      'required' => false, 'widget' => 'date',
             'aliases' => ['anniversary','wedding date','anniversary date']],
            ['key' => 'notes',        'label' => 'Notes',            'required' => false, 'widget' => 'text',
             'aliases' => ['notes','note','remarks','comment','comments']],
            ['key' => 'loyalty_points','label' => 'Loyalty points',  'required' => false, 'widget' => 'number', 'default' => '0',
             'aliases' => ['loyalty_points','points','loyalty','reward points','rewards']],
        ];
    }

    public function page()
    {
        return view('admin/import/index', ['pageTitle' => 'Data Import']);
    }

    // ── Step 1: upload, parse, return headers + mapping fields ─
    public function preview()
    {
        $type = $this->request->getPost('type');
        if (!in_array($type, self::TYPES, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unknown import type']);
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Choose a file to upload']);
        }
        if ($file->getSize() > 8 * 1024 * 1024) {
            return $this->response->setJSON(['success' => false, 'message' => 'File is too large (max 8 MB)']);
        }

        $reader = new FileTableReader();
        if (!$reader->read($file->getTempName(), $file->getClientName())) {
            return $this->response->setJSON(['success' => false, 'message' => $reader->error]);
        }

        $rid   = session('restaurant_id');
        $fields = $this->fieldsFor($type, (int)$rid);

        // sample rows for the mapping preview
        $rows = $reader->rows();
        $sample = array_slice($rows, 0, 8);

        session()->set('import_' . $type, [
            'headers'    => $reader->headers(),
            'rows'       => $rows,
            'file_name'  => $file->getClientName(),
            'imported_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'type'    => $type,
            'file'    => $file->getClientName(),
            'headers' => $reader->headers(),
            'sample'  => $sample,
            'fields'  => $fields,
            'total'   => count($rows),
        ]);
    }

    // ── Step 2: run with the mapping ──────────────────────────
    public function run()
    {
        $type = $this->request->getPost('type');
        if (!in_array($type, self::TYPES, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unknown import type']);
        }

        $mapping = json_decode($this->request->getPost('mapping'), true);
        if (!is_array($mapping)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid mapping']);
        }

        $data = session('import_' . $type);
        if (!$data) {
            return $this->response->setJSON(['success' => false, 'message' => 'Upload expired — start again with your file']);
        }

        $rid = session('restaurant_id');
        $fields = $this->fieldsFor($type, (int)$rid);

        // required-field check BEFORE importing anything
        foreach ($fields as $f) {
            $m = $mapping[$f['key']] ?? null;
            $hasColumn = $m && ($m['mode'] ?? '') === 'column';
            $hasCustom = $m && ($m['mode'] ?? '') === 'custom' && trim((string)($m['value'] ?? '')) !== '';
            if ($f['required'] && !$hasColumn && !$hasCustom) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => '"' . $f['label'] . '" is required — pick a file column or enter a custom value for every row',
                ]);
            }
        }

        $db = \Config\Database::connect();

        $results = ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
        foreach ($data['rows'] as $i => $raw) {
            $rowNum = $i + 2; // +2: header row is 1, data rows start at Excel row 2
            try {
                $out = $type === 'menu'
                    ? $this->importMenuRow($db, $mapping, $raw, (int)$rid, $i)
                    : $this->importCustomerRow($db, $mapping, $raw, (int)$rid, $i);
                if ($out['action'] === 'imported' || $out['action'] === 'updated') $results[$out['action']]++;
                else $results['skipped']++;
                if (!empty($out['note'])) $results['errors'][] = ['row' => $rowNum, 'message' => $out['note']];
            } catch (\Throwable $e) {
                $results['skipped']++;
                $results['errors'][] = ['row' => $rowNum, 'message' => $e->getMessage()];
            }
        }

        session()->remove('import_' . $type);

        return $this->response->setJSON(['success' => true, 'results' => $results, 'type' => $type]);
    }

    // ── one menu row ──────────────────────────────────────────
    private function importMenuRow($db, array $mapping, array $raw, int $rid, int $i): array
    {
        $v = function (string $key) use ($mapping, $raw) {
            $m = $mapping[$key] ?? null;
            if (!$m) return null;
            if (($m['mode'] ?? '') === 'column') return trim((string)($raw[(int)($m['col'] ?? 0)] ?? ''));
            return trim((string)($m['value'] ?? ''));
        };

        $name = $v('name');
        if ($name === '' || $name === null) {
            return ['action' => 'skipped', 'note' => 'Item name is empty'];
        }

        $price = $this->parseAmount($v('base_price'));
        if ($price === null || $price <= 0) {
            return ['action' => 'skipped', 'note' => 'Price "' . $v('base_price') . '" is not a valid amount'];
        }

        // duplicate guard (name, same restaurant)
        $exists = $db->table('menu_items')
            ->where('restaurant_id', $rid)
            ->where('LOWER(name)', strtolower($name))
            ->countAllResults();
        if ($exists) {
            return ['action' => 'skipped', 'note' => 'Item "' . $name . '" already exists'];
        }

        // category: pick by name, else create it, else "Imported Items"
        $categoryId = null;
        $catVal = $v('category');
        if ($catVal !== '' && $catVal !== null) {
            if (is_numeric($catVal)) {
                $cat = $db->table('menu_categories')->where('id', (int)$catVal)->where('restaurant_id', $rid)->get()->getRowArray();
                if ($cat) $categoryId = (int)$cat['id'];
            }
            if (!$categoryId) {
                $cat = $db->table('menu_categories')
                    ->where('restaurant_id', $rid)->where('LOWER(name)', strtolower($catVal))
                    ->get()->getRowArray();
                if ($cat) $categoryId = (int)$cat['id'];
            }
            if (!$categoryId) {
                // auto-create category from the file
                $db->table('menu_categories')->insert([
                    'restaurant_id' => $rid, 'name' => $catVal, 'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $categoryId = (int)$db->insertID();
            }
        }
        if (!$categoryId) {
            $cat = $db->table('menu_categories')
                ->where('restaurant_id', $rid)->where('LOWER(name)', 'imported items')->get()->getRowArray();
            if ($cat) {
                $categoryId = (int)$cat['id'];
            } else {
                $db->table('menu_categories')->insert([
                    'restaurant_id' => $rid, 'name' => 'Imported Items', 'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $categoryId = (int)$db->insertID();
            }
        }

        $tax = $this->parseAmount($v('tax_percent'));
        if ($tax === null) $tax = 12.00;
        $itemType = $this->parseItemType($v('item_type'));
        $foodType = $this->parseFoodType($v('food_type'));

        $db->table('menu_items')->insert([
            'restaurant_id' => $rid,
            'category_id'   => $categoryId,
            'name'          => $name,
            'description'   => $v('description') ?: null,
            'sku'           => $v('sku') ?: null,
            'item_type'     => $itemType,
            'food_type'     => $foodType,
            'base_price'    => $price,
            'tax_percent'   => $tax,
            'tax_type'      => 'exclusive',
            'sort_order'    => $i,
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return ['action' => 'imported'];
    }

    // ── one customer row ──────────────────────────────────────
    private function importCustomerRow($db, array $mapping, array $raw, int $rid, int $i): array
    {
        $v = function (string $key) use ($mapping, $raw) {
            $m = $mapping[$key] ?? null;
            if (!$m) return null;
            if (($m['mode'] ?? '') === 'column') return trim((string)($raw[(int)($m['col'] ?? 0)] ?? ''));
            return trim((string)($m['value'] ?? ''));
        };

        $name = $v('name');
        if ($name === '') return ['action' => 'skipped', 'note' => 'Name is empty'];

        $phone = preg_replace('/[^0-9]/', '', (string)$v('phone'));
        if (strlen($phone) < 10) return ['action' => 'skipped', 'note' => 'Phone "' . $v('phone') . '" is not valid'];

        // duplicate by phone — skip and report
        $dup = $db->table('customers')->where('restaurant_id', $rid)->where('phone', $phone)->countAllResults();
        if ($dup) return ['action' => 'skipped', 'note' => 'Phone ' . $phone . ' already exists'];

        $dob = $this->parseDate($v('dob'));
        $ann = $this->parseDate($v('anniversary'));
        $pts = $this->parseAmount($v('loyalty_points'));

        $db->table('customers')->insert([
            'restaurant_id'  => $rid,
            'name'           => $name,
            'phone'          => $phone,
            'email'          => filter_var($v('email'), FILTER_VALIDATE_EMAIL) ? $v('email') : null,
            'address'        => $v('address') ?: null,
            'city'           => $v('city') ?: null,
            'pincode'        => $v('pincode') ?: null,
            'gender'         => $this->parseGender($v('gender')),
            'dob'            => $dob,
            'anniversary'    => $ann,
            'notes'          => $v('notes') ?: null,
            'loyalty_points' => $pts ?? 0,
            'source'         => 'other',
            'is_active'      => 1,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        return ['action' => 'imported'];
    }

    // ── value normalisers ─────────────────────────────────────
    private function parseAmount($val): ?float
    {
        $val = str_replace([',', ' ', '₹', 'Rs', 'rs', 'INR', "\xC2\xA0"], '', (string)$val);
        if ($val === '' || !is_numeric($val)) return null;
        return round((float)$val, 2);
    }

    private function parseItemType($val): string
    {
        $val = strtolower(str_replace([' ', '-', '_'], '', (string)$val));
        if (str_contains($val, 'eggetarian') || $val === 'egg') return 'egg';
        if (str_contains($val, 'vegan') || str_contains($val, 'pureveg')) return 'vegan';
        if (str_ends_with($val, 'nonveg') || str_contains($val, 'non') || str_contains($val, 'nonveg')) return 'non_veg';
        if (str_contains($val, 'veg')) return 'veg';
        return 'veg';
    }

    private function parseFoodType($val): string
    {
        $val = strtolower((string)$val);
        if (str_contains($val, 'beverage') || str_contains($val, 'drink') || str_contains($val, 'juice')
            || str_contains($val, 'shake') || str_contains($val, 'coffee') || str_contains($val, 'tea')) return 'beverage';
        if (str_contains($val, 'dessert') || str_contains($val, 'sweet') || str_contains($val, 'cake')) return 'dessert';
        if (str_contains($val, 'combo')) return 'combo';
        if (str_contains($val, 'other')) return 'other';
        return 'food';
    }

    private function parseGender($val): ?string
    {
        $val = strtolower(str_replace(' ', '', (string)$val));
        if (in_array($val, ['m', 'male'], true) || str_starts_with($val, 'mal')) return 'male';
        if (in_array($val, ['f', 'female'], true) || str_starts_with($val, 'fem')) return 'female';
        if (in_array($val, ['other', 'o'], true)) return 'other';
        return null;
    }

    private function parseDate($val): ?string
    {
        $val = trim((string)$val);
        if ($val === '') return null;
        // common Indian formats
        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'd.m.Y', 'm/d/Y', 'd-M-Y', 'd/m/y', 'Ymd'];
        foreach ($formats as $f) {
            $d = \DateTime::createFromFormat($f, $val);
            if ($d && $d->format($f) === $val) {
                $y = (int)$d->format('Y');
                if ($y >= 1930 && $y <= (int)date('Y')) return $d->format('Y-m-d');
            }
        }
        return null;
    }
}
