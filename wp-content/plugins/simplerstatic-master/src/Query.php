<?php
namespace SimplerStatic;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Simpler Static Query class
 *
 * Used for creating queries for the WordPress database
 */
class Query {
    /**
     * @var Model
     */
    protected $model;

    /**
     * Maximum number of rows to return
     *
     * @var integer
     */
    protected $limit = null;

    /**
     * Skip this many rows before returning results
     *
     * @var integer
     */
    protected $offset = null;

    /**
     * @var mixed[]
     */
    protected $where = [];

    /**
     * @var string
     */
    protected $order = null;

    /**
     * @param Model $model
     */
    public function __construct( $model ) {
        $this->model = $model;
    }

    /**
     * Execute the query and return an array of models
     *
     * @return mixed[]
     */
    public function find() {
        global $wpdb;

        $model = $this->model;
        $query = $this->compose_select_query();

        $rows = $wpdb->get_results(
            $query,
            ARRAY_A
        );

        if ( $rows === null ) {
            return [];
        } else {
            $records = [];

            foreach ( $rows as $row ) {
                $records[] = $model::initialize( $row );
            }

            return $records;
        }
    }

    /**
     * First and return the first record matching the conditions
     *
     * @return mixed An instance of the class, or null
     */
    public function first() {
        global $wpdb;

        $model = $this->model;

        $this->limit( 1 );
        $query = $this->compose_select_query();

        $attributes = $wpdb->get_row(
            $query,
            ARRAY_A
        );

        if ( $attributes === null ) {
            return null;
        } else {
            return $model::initialize( $attributes );
        }
    }

    /**
     * Find and return the first record matching the column name/value
     *
     * Example: find_by( 'id', 123 )
     *
     * @param  string $column_name The name of the column to search on
     * @param  string $value       The value that the column should contain
     * @return mixed         An instance of the class, or null
     */
    public function find_by( $column_name, $value ) {
        global $wpdb;

        $model = $this->model;
        $this->where( [ $column_name => $value ] );

        $query = $this->compose_select_query();

        $attributes = $wpdb->get_row(
            $query,
            ARRAY_A
        );

        if ( $attributes === null ) {
            return null;
        } else {
            return $model::initialize( $attributes );
        }
    }

    /**
     * Find or initialize the first record with the given column name/value
     *
     * Finds the first record with the given column name/value, or initializes
     * an instance of the model if one is not found.
     *
     * @param  string $column_name The name of the column to search on
     * @param  string $value       The value that the column should contain
     * @return mixed              An instance of the class (might not exist in db yet)
     */
    public function find_or_initialize_by( $column_name, $value ) {
        global $wpdb;

        $model = $this->model;

        $obj = $this->find_by( $column_name, $value );
        if ( ! $obj ) {
            $obj = $model::initialize( [ $column_name => $value ] );
        }

        return $obj;
    }

    /**
     * Find the first record with the given column name/value, or create it
     *
     * @param  string $column_name The name of the column to search on
     * @param  string $value       The value that the column should contain
     * @return static              An instance of the class (might not exist in db yet)
     */
    public function find_or_create_by( $column_name, $value ) {
        $obj = $this->find_or_initialize_by( $column_name, $value );
        if ( ! $obj->exists() ) {
            $obj->save();
        }
        return $obj;
    }

    /**
     * Update all records to set the column name equal to the value
     *
     * String:
     * A single string, without additional args, is passed as-is to the query.
     * update_all( "widget_id = 2" )
     *
     * Assoc. array:
     * An associative array will use the keys as fields and the values as the
     * values to be updated.
     * update_all( array( 'widget_id' => 2, 'type' => 'sprocket' ) )
     *
     * @param  mixed $arg See description
     * @return int|null   The number of rows updated, or null if failure
     * @throws SimplerStaticException
     */
    public function update_all( $arg ) {
        if ( func_num_args() > 1 ) {
            throw new SimplerStaticException( 'Too many arguments passed' );
        }

        global $wpdb;

        $query = $this->compose_update_query( $arg );
        $rows_updated = $wpdb->query( $query );

        return $rows_updated;
    }

    /**
     * Delete records matching a where query, replacing ? with $args
     *
     * @return int|null   The number of rows deleted, or null if failure
     */
    public function delete_all() {
        global $wpdb;

        $query = $this->compose_query( 'DELETE FROM ' );
        $rows_deleted = $wpdb->query( $query );

        return $rows_deleted;
    }

    /**
     * Execute the query and return a count of records
     *
     * @return int|null
     */
    public function count() {
        global $wpdb;

        $query = $this->compose_select_query( 'COUNT(*)' );

        return $wpdb->get_var( $query );
    }

    /**
     * Set the maximum number of rows to return
     *
     * @param  integer $limit
     * @return self
     */
    public function limit( $limit ) {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set the number of rows to skip before returning results
     *
     * @param  integer $offset
     * @return self
     * @throws SimplerStaticException
     */
    public function offset( $offset ) {
        if ( $this->limit === null ) {
            throw new SimplerStaticException( 'Cannot offset without limit' );
        }

        $this->offset = $offset;
        return $this;
    }

    /**
     * Set the ordering for results
     *
     * @param  string $order
     * @return self
     */
    public function order( $order ) {
        $this->order = $order;
        return $this;
    }

    /**
     * Add a where clause to the query
     *
     * String:
     * A single string, without additional args, is passed as-is to the query.
     * where( "widget_id = 2" )
     *
     * assoc. array:
     * An associative array will use the keys as fields and the values as the
     * values to be searched for to create a condition.
     * where( array( 'widget_id' => 2, 'type' => 'sprocket' ) )
     *
     * string + args:
     * A string with placeholders '?' and additional args will have the string
     * treated as a template and the remaining args inserted into the template
     * to create a condition.
     * where( 'widget_id > ? AND widget_id < ?', 12, 18 )
     *
     * @param  mixed $arg See description
     * @return self
     * @throws SimplerStaticException
     */
    public function where( $arg ) {
        if ( func_num_args() == 1 ) {
            if ( is_array( $arg ) ) {
                // add array of conditions to the "where" array
                foreach ( $arg as $column_name => $value ) {
                    $this->where[] = self::where_sql( $column_name, $value );
                }
            } elseif ( is_string( $arg ) ) {
                // pass the string as-is to our "where" array
                $this->where[] = $arg;
            } else {
                throw new SimplerStaticException(
                    'One argument provided and it was not a string or array'
                );
            }
        } elseif ( func_num_args() > 1 ) {
            $where_values = func_get_args();
            $condition = array_shift( $where_values );

            if ( is_string( $condition ) ) {
                // check that the number of args and ?'s matches
                if ( substr_count( $condition, '?' ) != count( $where_values ) ) {
                    throw new SimplerStaticException(
                        "Number of arguments does not match number of placeholders (?'s)"
                    );
                } else {
                    // create a condition to add to the "where" array
                    foreach ( $where_values as $value ) {
                        $condition = preg_replace(
                            '/\?/',
                            self::escape_and_quote( $value ),
                            (string) $condition,
                            1
                        );
                    }

                    $this->where[] = $condition;
                }
            } else {
                throw new SimplerStaticException(
                    'Multiple arguments provided but first arg was not a string'
                );
            }
        } else {
            throw new SimplerStaticException( 'No arguments provided' );
        }

        return $this;
    }

    /**
     * Generate a SQL query for selecting records
     *
     * @param  string $fields Fields to select (null = all records)
     * @return string         The SQL query for selecting records
     */
    private function compose_select_query( $fields = null ) {
        $select = '';

        if ( $fields ) {
            $select = $fields;
        } else {
            $select = '*';
        }

        $statement = "SELECT {$select} FROM ";

        return $this->compose_query( $statement );
    }

    /**
     * Generate a SQL query for updating records
     *
     * String:
     * A single string, without additional args, is passed as-is to the query.
     * compose_update_query( "widget_id = 2" )
     *
     * Assoc. array:
     * An associative array will use the keys as fields and the values as the
     * values to be updated to create a condition.
     * compose_update_query( array( 'widget_id' => 2, 'type' => 'sprocket' ) )
     *
     * @param  mixed $arg See description
     * @throws SimplerStaticException
     */
    private function compose_update_query( $arg ) : string {
        $values = ' SET ';

        if ( is_array( $arg ) ) {
            // add array of conditions to the "where" array
            foreach ( $arg as $column_name => $value ) {
                $value = self::escape_and_quote( $value );
                $values .= "{$column_name} = $value ";
            }
        } elseif ( is_string( $arg ) ) {
            // pass the string as-is to our "where" array
            $values .= $arg . ' ';
        } else {
            throw new SimplerStaticException( 'Argument provided was not a string or array' );
        }

        return $this->compose_query( 'UPDATE ', $values );
    }

    /**
     * Generate a SQL query
     *
     * @param  string $statement SELECT *, UPDATE, etc.
     */
    private function compose_query( $statement, string $values = '' ) : string {
        $model = $this->model;
        $table  = ' ' . $model::table_name();
        $where  = '';
        $order  = '';
        $limit  = '';
        $offset = '';

        foreach ( $this->where as $condition ) {
            $where .= ' AND ' . $condition;
        }

        if ( $where !== '' ) {
            $where = ' WHERE 1=1' . $where;
        }

        if ( $this->order ) {
            $order = ' ORDER BY ' . $this->order;
        }

        if ( $this->limit ) {
            $limit = ' LIMIT ' . $this->limit;
        }

        if ( $this->offset ) {
            $offset = ' OFFSET ' . $this->offset;
        }

        $query = "{$statement}{$table}{$values}${where}{$order}{$limit}{$offset}";
        return $query;
    }

    /**
     * Generate a SQL fragment for use in WHERE x=y
     *
     * @param  string $column_name The name of the column
     * @param  mixed  $value       The value for the column
     * @return string              The SQL fragment to be used in WHERE x=y
     */
    private static function where_sql( $column_name, $value ) {
        $where_sql = $column_name;
        $where_sql .= ( $value === null ) ? ' IS ' : ' = ';
        $where_sql .= self::escape_and_quote( $value );
        return $where_sql;
    }

    /**
     * @param  mixed  $value
     */
    private static function escape_and_quote( $value ) : string {
        if ( $value === null ) {
            return 'NULL';
        } else {
            $value = esc_sql( $value );

            if ( is_string( $value ) ) {
                return "'{$value}'";
            }
        }
    }
}
