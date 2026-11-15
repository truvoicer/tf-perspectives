import { usePage } from '@inertiajs/react';

import {
    NiceSelectDropdown,
    TextInput,
    useAuthContext,
    useToastContext,
    StylingOptions,
    StylingOptionsProps,
    BlockOptionsSharedProps,
} from '@truvoicer/tru-fetcher-core-ui';

import {
    AppMiddleware,
} from '@truvoicer/tru-fetcher-core-ui/lib';
import {
    FetcherListPageBlock,
    SelectOption,
} from '@truvoicer/tru-fetcher-core-ui/types';
import SharedPageSelectOptions from './common/shared-page-select-options';
export interface BlockStatsOptionsProps extends BlockOptionsSharedProps {

}
function BlockStatsOptions({
    block,
    columnBlockDeleteFileRequest,
    columnBlockFileStoreRequest,
}: BlockStatsOptionsProps) {
    const data = block?.data as FetcherListPageBlock;
    const schema = block?.schema;
    const errors = block?.errors;

    const auth = useAuthContext();
    const page = usePage();

    const toastProvider = useToastContext();
    const middleware = new AppMiddleware(page);
    middleware.setToastProvider(toastProvider).setAuthProvider(auth);


    return (
        <div className="row align-items-center">
            <div className="col-12">
                <div className="card">
                    <div className="card-header">Fetch Options</div>
                    <div className="card-body">
                        <div className="row align-items-center">
                            <div className="col-md-6 col-12 mt-3">
                                <TextInput
                                    label="Search Query"
                                    type="text"
                                    id="query"
                                    name="query"
                                    className="single-input mt-2"
                                    placeholder="Search Query"
                                    defaultValue={(data?.query as string | null) || (schema?.query as string | null) || ''}
                                    error={errors?.query as string | undefined}
                                />
                            </div>
                            <div className="col-md-6 col-12 mt-3">
                                <TextInput
                                    label="Page Number"
                                    type="number"
                                    id="page_number"
                                    name="page_number"
                                    className="single-input mt-2"
                                    placeholder="Page Number"
                                    defaultValue={
                                        (data?.page_number as string | number | readonly string[] | undefined) ||
                                        (schema?.page_number as string | number | readonly string[] | undefined) ||
                                        ''
                                    }
                                    error={errors?.page_number as string | undefined}
                                />
                            </div>
                            <div className="col-md-6 col-12 mt-3">
                                <TextInput
                                    label="Page Size"
                                    type="number"
                                    id="page_size"
                                    name="page_size"
                                    className="single-input mt-2"
                                    placeholder="Page Size"
                                    defaultValue={
                                        (data?.page_size as string | number | readonly string[] | undefined) ||
                                        (schema?.page_size as string | number | readonly string[] | undefined) ||
                                        ''
                                    }
                                    error={errors?.page_size as string | undefined}
                                />
                            </div>
                            <div className="col-md-6 col-12 mt-3">
                                <TextInput
                                    label="Sort By"
                                    type="text"
                                    id="sort_by"
                                    name="sort_by"
                                    className="single-input mt-2"
                                    placeholder="Sort By"
                                    defaultValue={
                                        (data?.sort_by as string | number | readonly string[] | undefined) ||
                                        (schema?.sort_by as string | number | readonly string[] | undefined) ||
                                        ''
                                    }
                                    error={errors?.sort_by as string | undefined}
                                />
                            </div>
                            <div className="col-md-6 col-12 mt-3">
                                <label className="form-label mt-2">Sort Order</label>
                                <NiceSelectDropdown
                                    loadMoreOnScroll={false}
                                    placeholder="Sort Order (direction)"
                                    name="sort_order"
                                    options={[
                                        { value: 'asc', label: 'Ascending' },
                                        { value: 'desc', label: 'Descending' },
                                    ]}
                                    initialValue={(data?.sort_order as string) || (schema?.sort_order as string) || ''}
                                />
                            </div>
                            <div className="col-md-6 col-12 mt-3">
                                <TextInput
                                    label="Search Fields"
                                    type="text"
                                    id="search_fields"
                                    name="search_fields"
                                    className="single-input mt-2"
                                    placeholder="Search Fields"
                                    defaultValue={
                                        Array.isArray(data?.search_fields) ? (data.search_fields as string[]).join(', ') : (data?.search_fields ?? '')
                                    }
                                    error={errors?.search_fields as string | undefined}
                                />
                            </div>
                        </div>
                    </div>

                    <div className="card-header">Page</div>
                    <div className="card-body">
                        <SharedPageSelectOptions
                            block={block}
                        />
                    </div>

                    <div className="card-header">Styling</div>
                    <div className="card-body">
                        <div className="row align-items-center">
                            <StylingOptions
                                defaultValues={{
                                    background_color: '#ffffff',
                                    border_color: '#dee2e6',
                                    border_width: 1,
                                    border_style: 'solid',
                                    border_radius: '0px',
                                    box_shadow: 'none',
                                    padding: '15px',
                                    margin: '0px',
                                }}
                                enableThemeFields={true}
                                schema={schema}
                                data={data as StylingOptionsProps['data']}
                                errors={errors}
                                collapsible={true}
                                defaultExpanded={true}
                                title="Design & Styling"
                                showBackground={true}
                                showBorder={true}
                                showShadow={true}
                                showSpacing={true}
                            />
                        </div>
                    </div>

                </div>
            </div>

        </div>
    );
}

export default BlockStatsOptions;
