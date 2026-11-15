import { FetcherListStylingOptions, SharedBlockOptionsType } from "@truvoicer/tru-fetcher-core-ui";
import { Page } from "@truvoicer/tru-fetcher-core-ui/types";

export interface FeedOptionsBlock extends FetcherListStylingOptions, SharedBlockOptionsType {
    thread_page?: Page;
}

export interface ThreadOptionsBlock extends FetcherListStylingOptions, SharedBlockOptionsType {

}
export interface SharedPageSelectOptionsBlock  {
    search_page?: Page;
    search_results_page?: Page;
    thread_page?: Page;
    feed_page?: Page;
    categories_page?: Page;
    category_thread_page?: Page;
}
