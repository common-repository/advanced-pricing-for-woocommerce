<?php

defined( 'ABSPATH' ) || exit;

$args          = $args['args'];
$plugin_info   = $args['plugin_info'];
$product       = $args['product'];
$product_price = $args['product_price'];
$swatch_price  = $args['swatch_price'];
?>
<div class="<?php echo esc_attr( $plugin_info['prefix'] . '-quantity-swatch' ); ?><?php echo esc_attr( ( $args['has_default_swatch'] && ( $args['has_default_swatch'] === $args['pricing_row']['quantity'] ) ) ? ' active' : '' ); ?>" data-qty="<?php echo esc_attr( $args['pricing_row']['quantity'] ); ?>" >
	<?php if ( ! empty( $args['discount'] ) ) : ?>
	<div class="<?php echo esc_attr( $plugin_info['prefix'] . '-quantity-swatch__header' ); ?>">
		<span class="<?php echo esc_attr( $plugin_info['prefix'] . '-quantity-swatch__discount' ); ?>">
			<?php echo wp_kses_post( $args['discount'] ); ?>
		</span>
	</div>
	<?php endif; ?>
	<div class="<?php echo esc_attr( $plugin_info['prefix'] . '-quantity-swatch__body' ); ?>">
        <?php $qty_val = apply_filters( $plugin_info['prefix'] . '-quantity-swatch-qty', 'x' . $args['pricing_row']['quantity'], $args['pricing_row'], $product->get_id() ); ?>
		<span class="<?php echo esc_attr( $plugin_info['prefix'] . '-quantity-swatch__qty' ); ?>"><?php echo wp_kses_post( $qty_val ); ?></span>
		<span class="<?php echo esc_attr( $plugin_info['prefix'] . '-quantity-swatch__pricing' ); ?>"><?php echo wp_kses_post( $swatch_price ); ?></span>
	</div>
</div>
