<div class="pos-tab-content active">
    <div class="row">
    	<div class="col-sm-12">
    		<ul>
    			<li><?php echo __('woocommerce::lang.ins_1'); ?></li>
    			<li><?php echo __('woocommerce::lang.ins_2'); ?></li>
    			<li><?php echo __('woocommerce::lang.api_settings_help_text'); ?>. <a href="https://docs.woocommerce.com/document/woocommerce-rest-api/#section-3" target="_blank"><?php echo app('translator')->get('lang_v1.click_here'); ?></a> <?php echo app('translator')->get('lang_v1.for_more_info'); ?></li>
    			<li><?php echo __('woocommerce::lang.api_settings_help_permalink'); ?></li>
                <li><?php echo __('woocommerce::lang.api_settings_help_permalink_reset'); ?></li>
                <?php if(config('app.env') != 'demo' && auth()->user()->can('superadmin')): ?>
                    <li>
                        <p>
                            To <mark>Auto Sync</mark> categories, products and orders you must setup a cron job with this command:<br/>
                            <code><?php echo e($cron_job_command, false); ?></code>
                        </p>
                        
                        <p>
                            Set it in cron jobs tab in cpanel or directadmin or similar panel. <br/>Or edit crontab if using cloud/dedicated hosting. <br/>Or contact hosting for help with cron job settings.
                        </p>
                    </li>
                <?php endif; ?>
    		</ul>
    	</div>
    </div>
</div><?php /**PATH C:\laragon\www\Hassa POS\Modules\Woocommerce\Providers/../Resources/views/woocommerce/partials/api_instructions.blade.php ENDPATH**/ ?>