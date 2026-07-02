<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/**
 * Generic CRUD controller for metadata-driven modules (Config/modules.php).
 * Provides list / create / edit / delete with relation dropdowns, uploads,
 * audit logging and permission checks.
 */
class ResourceController extends Controller
{
    public function __construct(protected string $module, protected array $cfg)
    {
    }

    public function index(): void
    {
        Auth::require($this->cfg['perm_view']);
        $rows = Database::fetchAll(
            "SELECT * FROM `{$this->cfg['table']}` ORDER BY id DESC LIMIT 1000"
        );
        $this->view('resource/index', [
            'module' => $this->module,
            'cfg'    => $this->cfg,
            'rows'   => $rows,
            'labels' => $this->relationLabels(),
            'canManage' => Auth::can($this->cfg['perm_manage']),
        ]);
    }

    public function create(): void
    {
        Auth::require($this->cfg['perm_create'] ?? $this->cfg['perm_manage']);
        $this->form(null);
    }

    public function edit(int $id): void
    {
        Auth::require($this->cfg['perm_manage']);
        $row = Database::fetch("SELECT * FROM `{$this->cfg['table']}` WHERE id = ?", [$id]);
        $row ? $this->form($row) : redirect($this->module);
    }

    public function store(): void
    {
        Auth::require($this->cfg['perm_create'] ?? $this->cfg['perm_manage']);
        $this->save(null);
    }

    public function update(int $id): void
    {
        Auth::require($this->cfg['perm_manage']);
        $old = Database::fetch("SELECT * FROM `{$this->cfg['table']}` WHERE id = ?", [$id]);
        if (!$old) {
            redirect($this->module);
        }
        $this->save($old);
    }

    public function destroy(int $id): void
    {
        Auth::require($this->cfg['perm_manage']);
        $old = Database::fetch("SELECT * FROM `{$this->cfg['table']}` WHERE id = ?", [$id]);
        if ($old) {
            Database::delete($this->cfg['table'], $id);
            Audit::log('delete', $this->cfg['table'], (string) $id, $old);
            flash('success', $this->cfg['title'] . ' record deleted.');
        }
        redirect($this->module);
    }

    // -------------------------------------------------------------------------

    private function form(?array $row): void
    {
        $this->view('resource/form', [
            'module'  => $this->module,
            'cfg'     => $this->cfg,
            'row'     => $row,
            'options' => $this->relationOptions(),
        ]);
    }

    private function save(?array $old): void
    {
        $table = $this->cfg['table'];
        $data = [];
        try {
            foreach ($this->cfg['fields'] as $col => $field) {
                switch ($field['type']) {
                    case 'checkbox':
                        $data[$col] = isset($_POST[$col]) ? 1 : 0;
                        break;
                    case 'file':
                        $path = $this->storeUpload($col, $field['upload_dir'] ?? $this->module);
                        if ($path !== null) {
                            $data[$col] = $path;
                        } elseif ($old === null && ($field['required'] ?? false)) {
                            throw new \RuntimeException($field['label'] . ' is required.');
                        }
                        break;
                    case 'password':
                        $value = trim((string) ($_POST[$col] ?? ''));
                        if ($value !== '') {
                            if (!Auth::validPassword($value)) {
                                throw new \RuntimeException('Password must be 8+ characters with upper, lower, digit and symbol.');
                            }
                            $data['password_hash'] = password_hash($value, PASSWORD_BCRYPT);
                            $data['password_changed_at'] = date('Y-m-d H:i:s');
                        } elseif ($old === null) {
                            throw new \RuntimeException('Password is required for a new account.');
                        }
                        break;
                    default:
                        $value = trim((string) ($_POST[$col] ?? ''));
                        if ($value === '' && ($field['required'] ?? false)) {
                            throw new \RuntimeException($field['label'] . ' is required.');
                        }
                        $data[$col] = $value === '' ? null : $value;
                }
            }
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect($this->module . ($old ? "/{$old['id']}/edit" : '/create'));
        }

        if ($old !== null) {
            Database::update($table, $data, (int) $old['id']);
            Audit::log('update', $table, (string) $old['id'],
                array_intersect_key($old, $data), $data);
            flash('success', $this->cfg['title'] . ' updated.');
        } else {
            $id = Database::insert($table, $data);
            Audit::log('create', $table, (string) $id, null, $data);
            flash('success', $this->cfg['title'] . ' created.');
        }
        redirect($this->module);
    }

    /** value=>label maps for each relation field (for form dropdowns). */
    private function relationOptions(): array
    {
        $options = [];
        foreach ($this->cfg['fields'] as $col => $field) {
            if ($field['type'] === 'relation') {
                [$table, $expr] = $field['relation'];
                $rows = Database::fetchAll("SELECT id, {$expr} AS label FROM `{$table}` ORDER BY label LIMIT 2000");
                $options[$col] = array_column($rows, 'label', 'id');
            }
        }
        return $options;
    }

    /** Same maps, but only for columns shown in the list view. */
    private function relationLabels(): array
    {
        $labels = [];
        foreach ($this->cfg['list'] as $col) {
            $field = $this->cfg['fields'][$col] ?? null;
            if ($field && $field['type'] === 'relation') {
                [$table, $expr] = $field['relation'];
                $rows = Database::fetchAll("SELECT id, {$expr} AS label FROM `{$table}`");
                $labels[$col] = array_column($rows, 'label', 'id');
            }
        }
        return $labels;
    }
}
