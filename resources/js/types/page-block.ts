import { FetcherListStylingOptions, SharedBlockOptionsType } from "@truvoicer/tru-fetcher-core-ui";
import { Page } from "@truvoicer/tru-fetcher-core-ui/types";

export interface FeedOptionsBlock extends FetcherListStylingOptions, SharedBlockOptionsType {
    thread_page?: Page;
}
