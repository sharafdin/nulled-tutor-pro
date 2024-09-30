<?php
    $classes = array();
    try {
        $classes = $classroom->get_class_list();
    } catch ( \Exception $e ) {
        $message = $e->getMessage();
        $message = json_decode( $message );
        if ( is_object( $message ) && isset( $message->error ) ) {
            $error = $message->error;
            if ( isset( $error->message ) ) {
                $error_message = $error->message;
            } else {
                $error_message = __( 'Something went wrong, please check credential and permission!', 'tutor-pro' );
            }
        } else {
            $error_message = __( 'Something went wrong, please check credential and permission!', 'tutor-pro' );
        }
    }
?>

<?php if (isset( $error_message )) : ?>
<div class="tutor-alert tutor-bg-warning">
    <?php echo isset( $error_message ) ? esc_html( $error_message ) : ''; ?>
</div>
<?php endif; ?>

<div class="tutor-gc-filter-container tutor-row tutor-mb-24">
    <div class="tutor-col-md tutor-mb-16 tutor-mb-md-0">
        <div class="tutor-d-flex">
            <div class="tutor-mr-12">
                <select class="tutor-form-select">
                    <option value="import"><?php esc_html_e("Import", "tutor-pro"); ?></option>
                    <option value="publish"><?php esc_html_e("Publish", "tutor-pro"); ?></option>
                    <option value="trash"><?php esc_html_e("Trash", "tutor-pro"); ?></option>
                    <option value="delete" title="Only trashed classes can be deleted."><?php esc_html_e( 'Delete Permanently', 'tutor-pro' ); ?></option>
                    <option value="restore"><?php esc_html_e("Restore", "tutor-pro"); ?></option>
                </select>
            </div>
            <button class="tutor-btn tutor-btn-primary" id="tutor_gc_bulk_action_button"><?php esc_html_e( 'Apply', 'tutor-pro' ); ?></button>
        </div>
    </div>

    <div class="tutor-col-md-auto">
        <div class="tutor-form-wrap">
            <span class="tutor-form-icon"><span class="tutor-icon-search" area-hidden="true"></span></span>
            <input type="text" id="tutor-gc-search-class" class="tutor-form-control" placeholder="<?php esc_html_e( 'Search', 'tutor-pro' ); ?>" />
        </div>
    </div>
</div>

<?php if ( count( $classes ) ) : ?>
    <div class="tutor-table-responsive tutor-mb-32">
        <table class="tutor-table google-classroom-class-list">
            <thead>
                <tr>
                    <th width="1%">
                        <div class="tutor-d-flex tutor-option-field-row">
                            <input type="checkbox" id="tutor-bulk-checkbox-all" class="tutor-form-check-input">
                        </div>
                    </th>
                    <th><?php _e( 'Class Name', 'tutor-pro' ); ?></th>
                    <th><?php _e( 'Import Date', 'tutor-pro' ); ?></th>
                    <th><?php _e( 'Status', 'tutor-pro' ); ?></th>
                    <th><?php _e( 'Class Code', 'tutor-pro' ); ?></th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <?php
                    foreach ( $classes as $class ) :    
                    $is_imported = property_exists( $class, 'local_class_post' );
                    $permalink = $is_imported ? get_permalink( $class->local_class_post->ID ) : '';
                    $edit_link = $is_imported ? get_edit_post_link( $class->local_class_post->ID ) : '';
                    $post_id = $is_imported ? $class->local_class_post->ID : '';        
                ?>
                    <tr>
                        <td>
                            <div class="td-checkbox tutor-d-flex tutor-option-field-row">
                                <input type="checkbox" class="tutor-form-check-input tutor-bulk-checkbox" name="tutor-bulk-checkbox-all" value="<?php echo esc_attr( $class->id ); ?>">
                            </div>
                        </td>
                        <td class="tutor-gc-title">
                            <a class="tutor-color-black tutor-fs-6 tutor-fw-medium tutor-line-clamp-2" href="<?php echo esc_url( $class->alternateLink ); ?>" target="_blank">
                                <?php echo esc_html( $class->name ); ?>
                            </a>
                        </td>
                        <td>
                            <?php 
                                if ( $is_imported ) {
                                    echo get_post_meta( $post_id, 'tutor_gc_post_time', true );
                                }
                            ?>
                        </td>
                        <td>
                            <?php 
                                $status = $is_imported ? $class->local_class_post->post_status : 'Not Imported'; 
                                $status = ucfirst( $status );
                                $class_ = str_replace( ' ', '-', strtolower( $status ) );

                                $alert_class = 'primary';

                                switch ($class_) {
                                    case 'publish':
                                        $alert_class = 'success';
                                    break;
                                    
                                    case 'draft':
                                        $alert_class = 'warning';
                                    break;

                                    case 'trash':
                                        $alert_class = 'danger';
                                    break;

                                    default:
                                        $alert_class = 'primary';
                                    break;
                                }

                                echo '<span class="tutor-badge-label label-' . $alert_class . '" data-gc-status>' . $status . '</span>'; 
                            ?>
                        </td>
                        <td class="tutor-gc-code">
                            <?php echo esc_html( $class->enrollmentCode ); ?> <span class="tutor-iconic-btn tutor-copy-text" data-text="<?php echo esc_attr( $class->enrollmentCode ); ?>" role="button"><span class="tutor-icon-copy"></span></span>
                        </td>
                        <td data-class_actions id="tutor-gc-status" class="<?php echo 'class-status-' . $class_; ?>">
                            <button class="tutor-btn tutor-btn-outline-primary tutor-btn-md" data-action="import" data-classroom_id="<?php echo esc_attr( $class->id ); ?>"><?php esc_html_e( "Import", "tutor-pro" ); ?></button>
                            <button class="tutor-btn tutor-btn-primary tutor-btn-md class-preview-link" data-action="publish" data-class_post_id="<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Publish', 'tutor-pro' ); ?></button>
                            
                            <a target="_blank" href="<?php echo esc_url( $permalink ); ?>" class="tutor-btn tutor-btn-primary tutor-btn-md class-preview-link" data-action="preview"><?php esc_html_e( 'Preview', 'tutor-pro' ); ?></a>
                            <a href="<?php echo esc_url( $edit_link ); ?>" class="tutor-btn tutor-btn-outline-primary tutor-btn-md class-edit-link" data-action="edit"><?php esc_html_e( 'Edit', 'tutor-pro' ); ?></a>
                            
                            <button class="tutor-btn tutor-btn-primary tutor-btn-md" data-action="restore" data-class_post_id="<?php echo esc_attr( $post_id ); ?>"><?php esc_html_e( 'Restore', 'tutor-pro' ); ?></button>
                            
                            <button class="tutor-iconic-btn" data-action="trash" data-class_post_id="<?php echo esc_attr( $post_id ); ?>">
                                <span class="tutor-icon-trash-can" area-hidden="true"></span>
                            </button>
                            
                            <button class="tutor-iconic-btn" data-action="delete" data-prompt="Sure to delete permanently?" data-class_post_id="<?php echo esc_attr( $post_id ); ?>">
                                <span class="tutor-icon-trash-can" area-hidden="true"></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>