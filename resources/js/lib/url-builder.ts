import { route } from "ziggy-js";

export class UrlBuilder {
    static buildFeedPageUrl(feedPagePermalink?: string | null | undefined) {
        return feedPagePermalink ? `${feedPagePermalink}` : route('perspectives.index');
    }

    static buildThreadPageUrl(threadPagePermalink?: string | null | undefined, perspectiveId: number) {
        return threadPagePermalink ? `${threadPagePermalink}/${perspectiveId}` : `/perspectives/${perspectiveId}`;
    }

    static buildCategoriesPageUrl(categoriesPagePermalink?: string | null | undefined) {
        return categoriesPagePermalink ? `${categoriesPagePermalink}` : route('categories.index');
    }

    static buildCategoryThreadPageUrl(categoryThreadPagePermalink?: string | null | undefined, categoryId: number|string) {
        return categoryThreadPagePermalink ? `${categoryThreadPagePermalink}/${categoryId}` : `/categories/${categoryId}`;
    }

    static buildSearchPageUrl(searchPagePermalink?: string | null | undefined) {
        return searchPagePermalink ? `${searchPagePermalink}` : route('perspectives.search');
    }

    static buildSearchResultsPageUrl(searchResultsPagePermalink?: string | null | undefined) {
        return searchResultsPagePermalink ? `${searchResultsPagePermalink}` : route('perspectives.search');
    }
}
