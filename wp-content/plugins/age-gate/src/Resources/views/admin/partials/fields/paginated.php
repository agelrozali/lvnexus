<div class="ag-field--paginator" x-data="age_gate_pagination('<?php echo $field['taxonomy'] ?>')">
    <textarea name="ag_settings[terms][]" style="display: none;" x-model="selectedstring"></textarea>

    <div class="ag-paginator--tools" x-show="total || searchTerm">
        <strong><span x-text="selectedCount"></span> <?php echo esc_html(strtolower($field['label'])) ?> selected</strong>
        <span>
            <input type="text" placeholder="<?php echo esc_attr(__('Search')) ?>" x-model="searchText" @keydown.enter.prevent="search($el.value)" /> <button type="button" :disabled="!searchTerm" class="button button-link ag-pagninator--clear" @click="search(null)"><?php echo esc_html(__('Clear')) ?></button>
        </span>
    </div>
    <template x-if="loading">
        <div>
            <img src="/wp-admin/images/loading.gif" />
        </div>
    </template>
    <div class="ag-paginator--options" :style="loading ? 'opacity: .5' : ''">
        <template x-for="item in data" x-key="item.key">
            <div>
                <label class="ag-switch ag-switch--small">
                    <input type="checkbox" @change="selected[item.id] = $el.checked" :checked="selected[item.id]">
                    <span class="ag-switch__slider"></span>
                </label>
                <template x-if="item.lang">
                    <small><span x-text="item.lang"></span></small>
                </template>
                <span x-text="item.name"></span>
            </div>
        </template>
    </div>

    <div class="tablenav-pages">
        <button class="button button-link" @click="all()" x-show="total" type="button"><?php echo __('Select/Deselect All') ?></button>

        <div x-show="initialised">
            <span class="displaying-num"><span x-text="formattedTotal"></span> items</span>
            <span class="pagination-links">
                <template x-if="pages > 1">
                    <span>
                        <button type="button" class="first-page button" :disabled="current == 1 || loading" @click="page(1)"><span class="screen-reader-text"><?php echo __('First page') ?></span><span aria-hidden="true">«</span></button>
                        <button type="button" class="prev-page button" :disabled="current == 1 || loading" @click="page(current - 1)"><span class="screen-reader-text"><?php echo __('Previous page') ?></span><span aria-hidden="true">‹</span></button>
                    </span>
                </template>
                <template x-if="pages > 1">
                    <span class="paging-input">
                        <span>
                            <label for="current-page-selector" class="screen-reader-text"><?php echo __('Current Page') ?></label>
                            <input class="current-page" min="1" :max="pages" :disabled="loading" type="number" name="paged" :value="current" size="3" aria-describedby="table-paging" @keydown.enter.prevent="page($el.value)">
                        </span>

                        <span class="tablenav-paging-text"> of <span class="total-pages"><span x-text="pages"></span></span></span>
                    </span>
                </template>

                <template x-if="pages > 1">
                    <span>
                        <button type="button" class="next-page button" :disabled="current == pages || loading" @click="page(current + 1)"><span class="screen-reader-text"><?php echo __('Next page') ?></span><span aria-hidden="true">›</span></button>

                        <button type="button" class="last-page button" :disabled="current == pages || loading" @click="page(pages)"><span class="screen-reader-text"><?php echo __('Last page') ?></span><span aria-hidden="true">»</span></button>
                    </span>
                </template>
            </span>
        </div>
    </div>
</div>
