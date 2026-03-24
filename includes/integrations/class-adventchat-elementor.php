<?php
/**
 * Elementor integration — registers AdventChat widget in Elementor panel.
 *
 * WP-75: Elementor widget for launching the chat.
 *
 * @package AdventChat
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class AdventChat_Elementor
 */
class AdventChat_Elementor {

	/**
	 * Initialize Elementor integration.
	 */
	public static function init(): void {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return;
		}

		add_action( 'elementor/widgets/register', array( __CLASS__, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( __CLASS__, 'register_category' ) );
	}

	/**
	 * Register the widget category.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elementor elements manager.
	 */
	public static function register_category( $elements_manager ): void {
		$elements_manager->add_category( 'adventchat', array(
			'title' => __( 'AdventChat', 'adventchat' ),
			'icon'  => 'eicon-comments',
		) );
	}

	/**
	 * Register widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public static function register_widgets( $widgets_manager ): void {
		$widgets_manager->register( new AdventChat_Elementor_Widget() );
	}
}

if ( defined( 'ELEMENTOR_VERSION' ) ) {

	/**
	 * Elementor widget for AdventChat launcher button.
	 */
	class AdventChat_Elementor_Widget extends \Elementor\Widget_Base {

		public function get_name(): string {
			return 'adventchat-launcher';
		}

		public function get_title(): string {
			return __( 'AdventChat Launcher', 'adventchat' );
		}

		public function get_icon(): string {
			return 'eicon-comments';
		}

		public function get_categories(): array {
			return array( 'adventchat' );
		}

		protected function register_controls(): void {
			$this->start_controls_section( 'section_content', array(
				'label' => __( 'Content', 'adventchat' ),
			) );

			$this->add_control( 'button_text', array(
				'label'   => __( 'Button Text', 'adventchat' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Chat with us', 'adventchat' ),
			) );

			$this->add_control( 'button_color', array(
				'label'   => __( 'Button Color', 'adventchat' ),
				'type'    => \Elementor\Controls_Manager::COLOR,
				'default' => '',
			) );

			$this->add_control( 'button_align', array(
				'label'   => __( 'Alignment', 'adventchat' ),
				'type'    => \Elementor\Controls_Manager::CHOOSE,
				'options' => array(
					'left'   => array( 'title' => __( 'Left', 'adventchat' ), 'icon' => 'eicon-text-align-left' ),
					'center' => array( 'title' => __( 'Center', 'adventchat' ), 'icon' => 'eicon-text-align-center' ),
					'right'  => array( 'title' => __( 'Right', 'adventchat' ), 'icon' => 'eicon-text-align-right' ),
				),
				'default' => 'center',
			) );

			$this->end_controls_section();
		}

		protected function render(): void {
			$s     = $this->get_settings_for_display();
			$color = $s['button_color'] ? sprintf( 'background-color:%s;', esc_attr( $s['button_color'] ) ) : '';
			$align = $s['button_align'] ? sprintf( 'text-align:%s;', esc_attr( $s['button_align'] ) ) : '';

			printf(
				'<div style="%s"><button type="button" class="adventchat-elementor-btn" style="%s color:#fff;border:none;border-radius:8px;padding:12px 24px;font-size:15px;font-weight:600;cursor:pointer;" onclick="var l=document.getElementById(\'ac-launcher\');if(l)l.click();">%s</button></div>',
				esc_attr( $align ),
				esc_attr( $color ),
				esc_html( $s['button_text'] )
			);
		}
	}
}
