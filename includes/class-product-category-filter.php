<?php
defined('ABSPATH') || exit;

class IMQ_Product_Category_Filter
{
    /**
     * The single instance of the class.
     * 
     * @var IMQ_Product_Category_Filter
     * @access private
     */
    private static $_instance = null;

    /**
     * Private constructor to prevent instantiation from outside of the class.
     * 
     * @access private
     * @final
     */
    private final function __construct()
    {
        add_shortcode('imq-category-filter', array($this, 'product_category_filter_shortcode'));

        add_filter('request', array($this, 'category_filter_query_var'));
        add_action('pre_get_posts', array($this, 'product_filter_by_categories'));
        add_action('wp_footer', array($this, 'category_filter_scripts'));
    }


    /**
     * Generates a product category filter form as a shortcode.
     *
     * This shortcode function retrieves product categories using specified
     * arguments and constructs an HTML form with a dropdown list of categories.
     * Once a category is selected and the form is submitted, products are filtered
     * based on the selected category.
     *
     * @return string The HTML output for the category filter form or a message if no categories are found.
     */
    public function product_category_filter_shortcode()
    {
        $args = array(
            'taxonomy'   => 'product_cat',
            'orderby'    => 'name',
            'show_count' => 1,
            'pad_counts' => 0,
            'hierarchical' => 1,
            'title_li' => '',
            'hide_empty' => false,
        );

        $categories = get_terms($args);

        $current_url = home_url(add_query_arg(null, null));
        if ($categories && !is_wp_error($categories)) :

            $output = '<form id="imq-category-filter-form" method="get" action="' . esc_url($current_url) . '">';
            if (!empty($_GET)) {
                $shop_url = get_permalink(wc_get_page_id('shop'));

                $button_html = '<div class="clear-filters-container">';
                $button_html .= '<a href="' . esc_url($shop_url) . '" class="clear-filters">';
                $button_html .= 'Clear Filters';
                $button_html .= '</a>';
                $button_html .= '</div>';

                $output .= $button_html;
            }

            $output .= '<ul>';

            $output .= $this->build_category_list($categories);

            $output .= '</ul>';
            $output .= '<input type="submit" class="button" value="Filter Products" />';
            $output .= '</form>';

            return $output;
        else:
            return '<p>No categories found.</p>';
        endif;
    }

    /**
     * Generates an HTML list of categories with checkboxes, hierarchical and recursive.
     *
     * This function takes an array of categories and a parent ID as arguments. It
     * generates an HTML list of categories with checkboxes, hierarchical and
     * recursive. The function is called recursively for each category with children.
     *
     * @param array $categories The array of categories to generate the list from.
     * @param int $parent_id The ID of the parent category to generate the list for.
     *
     * @return string The generated HTML list of categories with checkboxes.
     */
    public function build_category_list($categories, $parent_id = 0)
    {
        $output = '';

        foreach ($categories as $category) {
            if ($category->parent == $parent_id) {
                $product_count = $category->count;

                $checked = isset($_GET['category']) && in_array($category->term_id, (array) $_GET['category']) ? 'checked' : '';


                $output .= '<li class="category-item">';
                $output .= '<div class="category-label">';

                $has_children = $this->category_has_children($category->term_id, $categories);
                $arrow = $has_children ? '<span class="expand-collapse " data-category="' . $category->term_id . '"><span class="imq-arrow "></span>' : '';

                $output .= $arrow;
                $output .= '<label><input type="checkbox" name="category[]" value="' . esc_attr($category->term_id) . '" ' . $checked . ' /> ';
                $output .= esc_html($category->name) . ' (' . $product_count . ')</label>';

                if ($has_children) {
                    $output .= '<ul class="subcategories" id="category-' . $category->term_id . '">';
                    $output .= $this->build_category_list($categories, $category->term_id);
                    $output .= '</ul>';
                }

                $output .= '</div>';
                $output .= '</li>';
            }
        }

        return $output;
    }

    /**
     * Adds JavaScript and CSS to the page footer to enable the category filter
     * functionality. The JavaScript code toggles the subcategory list when the
     * arrow icon is clicked. The CSS styles the category filter form and its
     * elements.
     *
     * @since 1.0.0
     */
    public function category_filter_scripts()
    {
?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.imq-arrow').on('click', function() {
                    const $arrow = $(this);
                    const categoryId = $arrow.closest('.expand-collapse').data('category');
                    const $subcategoryList = $('#category-' + categoryId);
                    $subcategoryList.slideToggle();
                    $arrow.toggleClass('close');
                });

                $('.subcategories input[type="checkbox"]').each(function() {
                    const isChecked = $(this).is(':checked');
                    if (isChecked) {
                        $(this).closest('.subcategories').toggle('close');
                    }

                })

                $('#imq-category-filter-form input[type="checkbox"]').on('change', function() {
                    $('#imq-category-filter-form').submit();
                });
            });
        </script>
        <style>
            .clear-filters-container {
                margin-bottom: 24px;
            }

            .clear-filters-container .clear-filters {
                background-color: #4981C6;
                color: white;
                padding: 10px 20px;
                text-decoration: none;
                border-color: #4981C6;
                border-radius: 5px;
            }

            #imq-category-filter-form {
                padding: 15px;
                /* border: 1px solid #ddd; */
                /* background-color: #f9f9f9; */
                margin-top: 20px;
            }

            #imq-category-filter-form ul {
                list-style-type: none;
                padding-left: 20px;
            }

            #imq-category-filter-form li {
                margin-bottom: 8px;
            }

            #imq-category-filter-form input[type="submit"] {
                padding: 8px 16px;
                background-color: #4981C6;
                color: white;
                border: none;
                cursor: pointer;
                margin-top: 10px;
                display: none;
            }

            #imq-category-filter-form input[type="submit"]:hover {
                background-color: #005f8c;
            }

            #imq-category-filter-form .category-label {
                display: inline-block;
            }

            #imq-category-filter-form .expand-collapse {
                cursor: pointer;
                margin-right: 10px;
                position: relative;
            }

            #imq-category-filter-form .subcategories {
                list-style-type: none;
                padding-left: 20px;
                display: none;
            }

            #imq-category-filter-form span.imq-arrow {
                position: absolute;
                display: inline-block;
                left: -20px;
                top: 2px;
                background-image: url('data:image/svg+xml,<svg height="800" width="800" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 511.787 511.787" xml:space="preserve"><path d="M508.667 125.707a10.623 10.623 0 0 0-15.04 0L255.76 363.573 18 125.707c-4.267-4.053-10.987-3.947-15.04.213a10.763 10.763 0 0 0 0 14.827L248.293 386.08a10.623 10.623 0 0 0 15.04 0l245.333-245.333c4.161-4.054 4.161-10.88.001-15.04z"/></svg>');
                background-size: contain;
                background-repeat: no-repeat;
                width: 14px;
                height: 14px;
                display: inline-block;
            }

            #imq-category-filter-form span.imq-arrow.close {
                transform: rotate(-90deg);
            }
        </style>
        </script>
<?php
    }


    /**
     * Modifies the query vars to include the category filter query var.
     *
     * Checks if the category query var is set in the $_GET array and if so,
     * adds it to the query vars array.
     *
     * @param array $query_vars The query vars array.
     * @return array The modified query vars array.
     */
    public function category_filter_query_var($query_vars)
    {
        if (isset($_GET['category'])) {
            $query_vars['category'] = $_GET['category'];
        }
        return $query_vars;
    }

    /**
     * Modifies the main query to filter products by categories.
     *
     * Checks if the category query var is set in the $_GET array and if so,
     * adds it to the tax query array.
     *
     * @param WP_Query $query The main query object.
     */
    public function product_filter_by_categories($query)
    {
        if (is_shop() || is_product_category() || is_product()) {
            if (isset($_GET['category']) && !empty($_GET['category'])) {
                $categories = $_GET['category'];
                if (is_array($categories)) {
                    $query->set('tax_query', array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field'    => 'id',
                            'terms'    => $categories,
                            'operator' => 'IN',
                        )
                    ));
                }
            }
        }
    }

    /**
     * Checks if a category has children.
     *
     * @param int    $category_id ID of the category to check.
     * @param object $categories  Array of all categories, as returned by get_terms().
     *
     * @return bool True if the category has children, false otherwise.
     */
    public function category_has_children($category_id, $categories)
    {
        foreach ($categories as $category) {
            if ($category->parent == $category_id) {
                return true;
            }
        }
        return false;
    }


    /**
     * Gets the singleton instance of the class.
     *
     * @return IMQ_Product_Category_Filter The singleton instance.
     */
    public static function get_instance()
    {
        if (! self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}

IMQ_Product_Category_Filter::get_instance();
