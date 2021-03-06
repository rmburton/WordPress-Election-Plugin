<?php 

class Post_Export {
	
	static function write_csv_row( $csv, $data, $order ) {
		$csv_data = array();
		foreach ( $order as $field_name )
		{
			$csv_data[] = $data[$field_name];
		}
		fputcsv( $csv, $csv_data );
	}
	
	static function export_post_csv( $csv, $post_type, $post_meta, $post_fields, $post_image_heading, $taxonomies ) {
		$meta_fields = empty( $post_meta ) ? array() : $post_meta->get_field_names();
		$headings = array();
		foreach ( array_merge( $post_fields, $taxonomies, $meta_fields) as $field ) {
			$headings[] = $field;
		}
		if ( $post_image_heading ) {
			$headings[] = "{$post_image_heading}_base64";
			$headings[] = "{$post_image_heading}_filename";
		}
		
		$heading_data = array_combine( $headings, $headings );
		self::write_csv_row( $csv, $heading_data, $headings );
		$args = array(
			'post_type' => $post_type,
			'orderby' => 'name',
			'order' => 'ASC',
			'nopaging' => true,
		);

		$query = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			if ( $post_meta ) {
				$data = $post_meta->get_field_values( $query->post->ID );
			}
			
			foreach ( $post_fields as $field => $label )
			{
				$data[$label] = $query->post->$field;
			}
						
			if ( $post_image_heading )
			{
				$image_id = get_post_thumbnail_id( $query->post->ID );
				if ( $image_id ){
					$image_meta = wp_get_attachment_metadata( $image_id );
					$upload_dir = wp_upload_dir();
					$image_filename = "{$upload_dir['basedir']}/{$image_meta['file']}";
					$data["{$post_image_heading}_base64"] = base64_encode( file_get_contents( $image_filename ) );
					$data["{$post_image_heading}_filename"] = basename( $image_filename );
				} else {
					$data["{$post_image_heading}_base64"] = '';
					$data["{$post_image_heading}_filename"] = '';
				}
			}

			foreach ( $taxonomies as $taxonomy_name => $taxonomy_field )
			{
				$terms = get_the_terms( $query->post->ID, $taxonomy_name );
				if ( isset( $terms[0] ) ) {
					$term = $terms[0];
					$data[$taxonomy_field] = $term->slug;
				} else {
					$data[$taxonomy_field] = '';
				}
			}
			
			self::write_csv_row( $csv, $data, $headings );
		}
	}

	static function export_taxonomy_csv ( $csv, $taxonomy, $taxonomy_name, $taxonomy_fields, $taxonomy_meta = null, $parent = null, $write_headings = true )
	{
		
		$meta_fields = empty( $taxonomy_meta ) ? array() : $taxonomy_meta->get_field_names( 'non_image' );
		$meta_image_fields = empty ( $taxonomy_meta ) ? array() : $taxonomy_meta->get_field_names( 'image' );
		$headings = array();
		foreach ( array_merge( $taxonomy_fields, $meta_fields ) as $field_name ) {
			$headings[] = $field_name;
		}
		
		foreach ( $meta_image_fields as $field_names ) {
			$headings[] = $field_names['base64'];
			$headings[] = $field_names['filename'];
		}
		
		if ( $write_headings ) {
			$headings_data = array_combine( $headings, $headings );
			self::write_csv_row( $csv, $headings_data, $headings );
		}
		
		$args = array(
			'hide_empty' => false,
			'fields' => 'all',
			'orderby' => 'name',
			'order' => 'ASC'
		);
		if ( $parent !== null ) {
			$args['parent'] = $parent;
		}
		$terms = get_terms( $taxonomy_name, $args );
		foreach ( $terms as $term ) {
			$data = empty( $taxonomy_meta ) ? array() : $taxonomy_meta->get_field_values( $term->term_id);
			foreach ( $taxonomy_fields as $field )
			{
				if ( 'parent' == $field ) {
					$parent_term = $term->parent ? get_term( $term->parent, $taxonomy_name ) : '';
					$data[$field] = $parent_term ? $parent_term->slug : '';
				} else {
					$data[$field] = $term->$field;
				}
			}
			
			self::write_csv_row( $csv, $data, $headings );
			
			if ( $parent !== null ) {
				self::export_taxonomy_csv ( $csv, $taxonomy, $taxonomy_name, $taxonomy_fields, $taxonomy_meta, $term->term_id, false );
			}
		}		
	}
}
