<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

/**
 * @magentooverride vendor/hyva-themes/magento2-smile-elasticsuite/src/view/frontend/templates/core/search/form.mini.phtml (v2.4.4)
 *
 * @copyright   Copyright (c) Y1 Digital AG (http://www.y1.de/)
 * @contact     info@y1.de
 */

declare(strict_types=1);

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Template;
use Magento\Search\Helper\Data as SearchHelper;
use TattooNeeds\Icons\ViewModel\TattooNeedsIcons;
use Hyva\Theme\Model\ViewModelRegistry;

/**
 * Template for quick search mini form.
 * Overridden to manage template injection for the rendering of autocomplete results.
 *
 * @var \Smile\ElasticsuiteCore\Block\Search\Form\Autocomplete $block
 * @var SearchHelper $helper
 * @var Escaper $escaper
 * @var \Hyva\Theme\Model\ViewModelRegistry $viewModels
 * @var \Hyva\Theme\ViewModel\HeroiconsOutline $heroicons
 */
$y1Icons = $viewModels->require(TattooNeedsIcons::class);

$helper        = $this->helper(SearchHelper::class);
$suggestionUrl = $helper->getResultUrl() . '?' . $helper->getQueryParamName() . '=';
$heroicons     = $viewModels->require(\Hyva\Theme\ViewModel\HeroiconsOutline::class);
$templates     = json_decode($block->getJsonSuggestRenderers(), true);

?>

<script>
    function initMiniSearchComponent() {
        "use strict";

        return {
            show:false,
            formSelector: "#search_mini_form",
            url: "<?= /* @escapeNotVerified */ $block->getUrl('search/ajax/suggest') ?>",
            destinationSelector: "#search_autocomplete",
            templates: <?= /* @noEscape */ $block->getJsonSuggestRenderers() ?>,
            priceFormat: <?= /* @noEscape */ $block->getJsonPriceFormat() ?>,
            minSearchLength: <?= /* @escapeNotVerified */ $helper->getMinQueryLength() ?>,
            searchResultsByType: {},
            termResults: [],
            categoryResults: [],
            productResults: [],
            currentRequest: null,

            /**
             * Get search results.
             */
            getSearchResults: function () {
                let value = document.querySelector('#search').value.trim();

                if (value.length < parseInt(this.minSearchLength, 10)) {
                    this.searchResultsByType = [];

                    return false;
                }

                let url = this.url + '?' + new URLSearchParams({
                    q: document.querySelector('#search').value,
                    _: Date.now()
                }).toString();

                if (this.currentRequest !== null) {
                    this.currentRequest.abort();
                }
                this.currentRequest = new AbortController();

                fetch(url, {
                    method: 'GET',
                    signal: this.currentRequest.signal,
                }).then((response) => {
                    if (response.ok) {
                        return response.json();
                    }
                }).then((data)  => {
                    this.show = data.length > 0;

                    this.termResults = data.filter(data => data.type === 'term')
                    console.log(JSON.parse(JSON.stringify(this.termResults)))
                    this.productResults = data.filter (data => data.type === 'product')
                    console.log(JSON.parse(JSON.stringify(this.productResults)))
                    this.categoryResults = data.filter (data => data.type === 'category')
                    console.log(JSON.parse(JSON.stringify(this.categoryResults)))

                    /*
                    this.searchResultsByType = data.reduce((acc, result) => {
                        if (! acc[result.type]) acc[result.type] = [];
                        acc[result.type].push(result);
                        return acc;
                    }, {});
                    */
                }).catch((error) => {
                    ;
                });
            },
            clearInput() {
                const search = this.$refs.searchInput;
                search.value = '';
                this.show = !this.show;
            },
            <!-- Add blur to Content/Background when Suggestion Field is open -->
        }
    }
</script>
<div id="search-content" x-show="true">
    <div class="mx-auto" x-data="initMiniSearchComponent()" @click.away="show = false">
        <form class="form minisearch border-none hover:border-none active:border-none focus:border-none" id="search_mini_form" action="<?= $escaper->escapeUrl($helper->getResultUrl()) ?>" method="get">
            <div class="">
                <label class="sr-only" for="search">
                    <?= $escaper->escapeHtmlAttr(__('Enter search term...')) ?>
                </label>
                <div class="flex content-width">
                    <input id="search"
                           x-on:input.debounce="getSearchResults()"
                           x-ref="searchInput"
                           type="search"
                           class="w-full p-2 text-lg placeholder-current text-text-dark-hint leading-normal transition appearance-none
                           copy-m h-[56px] border-transparent focus:border-transparent focus:ring-0"
                           autocapitalize="off" autocomplete="off" autocorrect="off"
                           name="<?= $escaper->escapeHtmlAttr($helper->getQueryParamName()) ?>"
                           value="<?= $escaper->escapeHtmlAttr($helper->getEscapedQueryText()) ?>"
                           placeholder="<?= $escaper->escapeHtmlAttr(__('Enter search term...')) ?>"
                           maxlength="<?= $escaper->escapeHtmlAttr($helper->getMaxQueryLength()) ?>"
                    />
                    <div class="flex">
                        <span x-show="show" @click="clearInput()" class="flex cursor-pointer items-center"><?= $y1Icons->renderHtml('icon/close', '', 24, 24);?></span>
                        <span x-show="!show" class="flex items-center"><?= $y1Icons->renderHtml('icon/search', '', 24, 24);?></span>
                    </div>
                </div>
            </div>
                <div id="search_autocomplete" class="search-autocomplete relative border-t w-full border-black" x-show="show" style="display:none;">
                    <div class="absolute bg-light-grey w-full">
                        <div class="z-50 bg-light-grey content-width w-auto flex flex-row h-[450px] mo:h-auto">

                            <div class="left-col">
                                <template x-for="termResults in Object.values(termResults)">
                                    <div>
                                        <template x-if="termResults.hasOwnProperty(0) && templates[termResults[0].type].title && templates[termResults[0].type].titleRenderer === undefined">
                                            <div class="mt-24 headline-m uppercase text-left" x-text="templates[termResults[0].type].title"></div>
                                        </template>
                                        <span class="flex mt-8 mb-16 items-center"><?= $y1Icons->renderHtml('icon/line', '', 180, 3);?></span>
                                        <template x-if="termResults.hasOwnProperty(0) && templates[termResults[0].type].titleRenderer !== undefined">
                                            <div class="mt-2 ml-2 text-left" x-text="window[templates[termResults[0].type].titleRenderer](termResults)"></div>
                                        </template>
                                        <template x-for="searchResult in termResults">
                                            <div class="hover:bg-gray-100">
                                                <?php foreach(json_decode($block->getJsonSuggestRenderers(), true) as $renderer): ?>
                                                    <?= $block->getLayout()
                                                        ->createBlock('Magento\Framework\View\Element\Template')
                                                        ->setTemplate($renderer['template'])
                                                        ->setData('suggestion_url', $suggestionUrl)
                                                        ->toHtml()
                                                    ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-for="categoryResults in Object.values(categoryResults)">
                                    <div>
                                        <template x-if="categoryResults.hasOwnProperty(0) && templates[categoryResults[0].type].title && templates[categoryResults[0].type].titleRenderer === undefined">
                                            <div class="mt-24 headline-m uppercase text-left" x-text="templates[categoryResults[0].type].title"></div>
                                        </template>
                                        <span class="flex mt-8 mb-16 items-center"><?= $y1Icons->renderHtml('icon/line', '', 180, 3);?></span>
                                        <template x-if="categoryResults.hasOwnProperty(0) && templates[categoryResults[0].type].titleRenderer !== undefined">
                                            <div class="mt-2 ml-2 text-left" x-text="window[templates[categoryResults[0].type].titleRenderer](categoryResults)"></div>
                                        </template>
                                        <template x-for="searchResult in categoryResults">
                                            <div class="hover:bg-gray-100">
                                                <?php foreach(json_decode($block->getJsonSuggestRenderers(), true) as $renderer): ?>
                                                    <?= $block->getLayout()
                                                        ->createBlock('Magento\Framework\View\Element\Template')
                                                        ->setTemplate($renderer['template'])
                                                        ->setData('suggestion_url', $suggestionUrl)
                                                        ->toHtml()
                                                    ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <div class="right-col">
                                <template x-for="productResults in Object.values(productResults)">

                                    <div class="flex flex-wrap flex-col flex-[0_0_50%]">
                                        <!-- Headlines -->
                                        <template x-if="productResults.hasOwnProperty(0) && templates[productResults[0].type].title && templates[productResults[0].type].titleRenderer === undefined">
                                            <div class="mt-24 headline-m uppercase text-left" x-text="templates[productResults[0].type].title"></div>
                                        </template>
                                        <span class="flex mt-8 mb-16 items-center"><?= $y1Icons->renderHtml('icon/line', '', 180, 3);?></span>

                                        <!-- Result Items -->
                                        <template x-if="productResults.hasOwnProperty(0) && templates[productResults[0].type].titleRenderer !== undefined">
                                            <div class="mt-2 ml-2 text-left" x-text="window[templates[productResults[0].type].titleRenderer](productResults)"></div>
                                        </template>

                                        <template x-for="searchResult in productResults">
                                            <div class="hover:bg-gray-100">
                                                <?php foreach(json_decode($block->getJsonSuggestRenderers(), true) as $renderer): ?>
                                                    <?= $block->getLayout()
                                                        ->createBlock('Magento\Framework\View\Element\Template')
                                                        ->setTemplate($renderer['template'])
                                                        ->setData('suggestion_url', $suggestionUrl)
                                                        ->toHtml()
                                                    ?>
                                                <?php endforeach; ?>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            <?= $block->getChildHtml() ?>
        </form>
    </div>
</div>
