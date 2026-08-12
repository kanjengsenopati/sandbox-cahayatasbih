<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

/**
 * Database Compatibility Helper.
 *
 * Menyediakan fungsi-fungsi SQL yang kompatibel antara MySQL dan SQLite.
 * Digunakan agar kode yang dikembangkan di lokal (SQLite) tetap berjalan
 * sempurna saat di-deploy ke VPS produksi (MySQL).
 */
class DbCompat
{
    /**
     * Get the current database driver name.
     */
    public static function driver(): string
    {
        return DB::connection()->getDriverName();
    }

    /**
     * Check if the current driver is SQLite.
     */
    public static function isSqlite(): bool
    {
        return static::driver() === 'sqlite';
    }

    /**
     * MONTH(column) equivalent.
     * MySQL: MONTH(col)  |  SQLite: CAST(strftime('%m', col) AS INTEGER)
     */
    public static function month(string $column): string
    {
        return static::isSqlite()
            ? "CAST(strftime('%m', {$column}) AS INTEGER)"
            : "MONTH({$column})";
    }

    /**
     * YEAR(column) equivalent.
     * MySQL: YEAR(col)  |  SQLite: CAST(strftime('%Y', col) AS INTEGER)
     */
    public static function year(string $column): string
    {
        return static::isSqlite()
            ? "CAST(strftime('%Y', {$column}) AS INTEGER)"
            : "YEAR({$column})";
    }

    /**
     * HOUR(column) equivalent.
     * MySQL: HOUR(col)  |  SQLite: CAST(strftime('%H', col) AS INTEGER)
     */
    public static function hour(string $column): string
    {
        return static::isSqlite()
            ? "CAST(strftime('%H', {$column}) AS INTEGER)"
            : "HOUR({$column})";
    }

    /**
     * CAST(column AS UNSIGNED) equivalent.
     * MySQL: CAST(col AS UNSIGNED)  |  SQLite: CAST(col AS INTEGER)
     *
     * Note: SQLite does not support UNSIGNED keyword in CAST.
     * Using INTEGER works identically on both databases.
     */
    public static function castUnsigned(string $column): string
    {
        return static::isSqlite()
            ? "CAST({$column} AS INTEGER)"
            : "CAST({$column} AS UNSIGNED)";
    }

    /**
     * Classroom sorting expression.
     * Used across multiple controllers for natural numeric sorting of classroom names.
     *
     * Returns a full ORDER BY clause fragment: "CAST(name AS INTEGER) ASC, name ASC"
     */
    public static function classroomOrder(string $column = 'name'): string
    {
        return static::castUnsigned($column) . " ASC, {$column} ASC";
    }

    /**
     * FIELD(column, val1, val2, ...) equivalent.
     * MySQL: FIELD(col, v1, v2, v3)
     * SQLite: CASE col WHEN v1 THEN 1 WHEN v2 THEN 2 ... ELSE 99 END
     */
    public static function field(string $column, array $values): string
    {
        if (static::isSqlite()) {
            $cases = [];
            foreach (array_values($values) as $idx => $val) {
                $escaped = is_numeric($val) ? $val : "'" . addslashes($val) . "'";
                $cases[] = "WHEN {$escaped} THEN " . ($idx + 1);
            }
            return "CASE {$column} " . implode(' ', $cases) . " ELSE 99 END";
        }

        $escaped = array_map(function ($val) {
            return is_numeric($val) ? $val : "'" . addslashes($val) . "'";
        }, $values);

        return "FIELD({$column}, " . implode(',', $escaped) . ")";
    }

    /**
     * CONCAT(parts...) equivalent.
     * MySQL: CONCAT(a, b, c)  |  SQLite: (a || b || c)
     */
    public static function concat(string ...$parts): string
    {
        if (static::isSqlite()) {
            return '(' . implode(' || ', $parts) . ')';
        }

        return 'CONCAT(' . implode(', ', $parts) . ')';
    }

    /**
     * LPAD(column, length, pad) equivalent.
     * MySQL: LPAD(col, len, pad)  |  SQLite: printf('%0Xd', col) where X is length
     *
     * Note: SQLite fallback only works for numeric zero-padding.
     */
    public static function lpad(string $column, int $length, string $pad = '0'): string
    {
        if (static::isSqlite() && $pad === '0') {
            return "printf('%0{$length}d', {$column})";
        }

        return "LPAD({$column}, {$length}, '{$pad}')";
    }

    /**
     * Build a date expression from bills.year and bills.month columns.
     * Used for date range filtering on bill records.
     *
     * MySQL: STR_TO_DATE(CONCAT(bills.year, '-', LPAD(bills.month, 2, '0'), '-01'), '%Y-%m-%d')
     * SQLite: (bills.year || '-' || printf('%02d', bills.month) || '-01')
     */
    public static function billDateExpr(string $yearCol = 'bills.year', string $monthCol = 'bills.month'): string
    {
        if (static::isSqlite()) {
            return "({$yearCol} || '-' || printf('%02d', {$monthCol}) || '-01')";
        }

        return "STR_TO_DATE(CONCAT({$yearCol}, '-', LPAD({$monthCol}, 2, '0'), '-01'), '%Y-%m-%d')";
    }

    /**
     * LIKE CONCAT('%', column, '%') equivalent.
     * MySQL: ? LIKE CONCAT('%', col, '%')
     * SQLite: ? LIKE ('%' || col || '%')
     */
    public static function likeContains(string $needle, string $haystackColumn): string
    {
        if (static::isSqlite()) {
            return "{$needle} LIKE ('%' || {$haystackColumn} || '%')";
        }

        return "{$needle} LIKE CONCAT('%', {$haystackColumn}, '%')";
    }
}
