import {
    Badge,
    Button,
    TextField,
    Card,
    Grid, Icon, IndexFilters, IndexTable,
    LegacyCard,
    Modal,
    Pagination, Select, Tag,
    Text,
    TextContainer,
    useIndexResourceState, useSetIndexFiltersMode
} from '@shopify/polaris';

// import { colourOptions } from 'data';

import { router, usePage } from '@inertiajs/react';
import { ArrowLeftIcon, SettingsFilledIcon } from "@shopify/polaris-icons";
import { useCallback, useEffect, useRef, useState } from 'react';
function LogsTable({ filter }) {

    const page = usePage().props;
    let timeout = null;

    const resourceName = {
        singular: 'log',
        plural: 'logs',
    };

    const pageOptions = [
        { label: '5', value: '5' },
        { label: '10', value: '10' },
        { label: '20', value: '20' },
        { label: '50', value: '50' },
        { label: '100', value: '100' },
    ];

    const [pageCount, setPageCount] = useState("10");

    const [tableRows, setTableRows] = useState([
        // name, code, brand, category,subcategory, unitcost
        {
            id: '',
            name: '',
            code: '',
            brand: '',
            category: '',
            subcategory: '',
            qty: <b style={{ fontWeight: '900', fontSize: '14px' }}>500</b>,
            unitcost: '30.00 EUR',
            unitcostUSD: <b style={{ fontWeight: '900', fontSize: '14px' }}>1000.00 EUR</b>,
            unitcostEGP: <b style={{ fontWeight: '900', fontSize: '14px' }}>1000.00 EGP</b>,
            costofKGUSD: <b style={{ fontWeight: '900', fontSize: '14px' }}>25.56 USD</b>,
            costofgmUSD: <b style={{ fontWeight: '900', fontSize: '14px' }}>0.03 USD</b>,
            unitweightGR: <b style={{ fontWeight: '900', fontSize: '14px' }}>185 gm</b>,
            unitcostIncludingweightUSD: <b style={{ fontWeight: '900', fontSize: '14px' }}>9.41 USD</b>,
            unitcostIncludingweightEGP: <b style={{ fontWeight: '900', fontSize: '14px' }}>9.41 EGP</b>,
            grossmargin: '45%',
            finalprice: <b style={{ fontWeight: '900', fontSize: '14px' }}>132,000.00 EGP</b>,
        },
        {
            id: '2',
            name: 'Kelvin',
            code: '424435',
            brand: 'CK Uwear',
            category: 'Clothing',
            subcategory: 'UWear',
            qty: '500',
            unitcost: '30.00 EUR',
            unitcostUSD: '40 USD',
            unitcostEGP: '300 EGP',
            costofKGUSD: '25.56 USD',
            costofgmUSD: '0.03 USD',
            unitweightGR: '185 gm',
            unitcostIncludingweightUSD: '9.41 USD',
            unitcostIncludingweightEGP: '486.88 EGP',
            grossmargin: '45%',
            finalprice: '720.50 EGP',
        }
    ]);

    const [selected, setSelected] = useState(0);
    const [itemStrings, setItemStrings] = useState([
        'All',
        'Name',
        'Code',
        'Brand',
        'Category',
    ]);
    const tabs = itemStrings.map((item, index) => ({
        content: item,
        index,
        onAction: () => { },
        id: `${item}-${index}`,
        isLocked: index === 0,
        actions: []
    }));

    const sortOptions = [
        { label: 'Id', value: 'id asc', directionLabel: 'Ascending' },
        { label: 'Id', value: 'id desc', directionLabel: 'Descending' },
    ];

    const [sortSelected, setSortSelected] = useState(['id desc']);
    const [queryValue, setQueryValue] = useState("");
    const { mode, setMode } = useSetIndexFiltersMode();
    const onHandleCancel = () => { };

    const [pagination, setPagination] = useState({
        path: route("ic_logs.list"),
        next_cursor: null,
        next_page_url: null,
        prev_cursor: null,
        prev_page_url: null,
    });
    const [currentCursor, setCurrentCursor] = useState(null);
    const [loading, setLoading] = useState(false);
    const [myUrl, setMyUrl] = useState("");
    const [reload, setReload] = useState(false);
    const { selectedResources, allResourcesSelected, handleSelectionChange } = useIndexResourceState(tableRows);
    const handlePageCount = useCallback((value) => { setPageCount(value); setCurrentCursor(null); setReload(!reload); }, [tableRows]);

    useEffect(() => {

        let url = new URL(pagination.path);

        url.searchParams.set('page_count', pageCount);

        if (currentCursor) {
            url.searchParams.set('cursor', currentCursor);
        }

        if (sortSelected != "") {
            url.searchParams.set('sort', sortSelected[0])
        } else {
            url.searchParams.delete('sort');
        }

        if (queryValue != '') {
            url.searchParams.set('q', queryValue);
        } else {
            url.searchParams.delete('q');
        }

        setMyUrl(url);
        url = url.toString();
        setLoading(true)
        fetch(url)
            .then((response) => response.json())
            .then((result) => {
                if (result.success == true) {
                    const my_rows = [];
                    result.data.data.forEach((log, index) => {
                        my_rows.push(
                            {
                                id: log.id,
                                created_at: new Date(log.created_at).toLocaleString(),
                                store: log.user?.store_name ? log?.user?.store_name : log?.user?.name,
                                type: <div style={{ textTransform: 'capitalize' }} >{log.type == "error" ?
                                    <div className='errorBadgeCust'><Badge tone="critical"> {log.type} </Badge></div> : log.type == "info" ? <div className='infoBadgeCust'><Badge tone="info"> {log.type} </Badge ></div> : log.type == "success" ? <div className='sucessBadgeCust'><Badge tone="success" > {log.type} </Badge> </div> : ""
                                }</div>,
                                priority: <div className={log.priority ? 'errorBadgeCust' : 'badgeWarning'}><Badge> {log.priority ? 'High' : 'Low'} </Badge></div>,
                                loggable_type: <div style={{ textTransform: 'capitalize' }} >{log.loggable_type == "App\\Models\\InConnect\\InconnectAccount" ?
                                    <div className='tagsAccCust'><Tag > Account </Tag> </div> : log.loggable_type == "App\\Models\\InConnect\\InconnectOrder" ? <div className='tagsOrderCust'><Tag> Order </Tag ></div > : log.loggable_type == "App\\Models\\InConnect\\Vendor" ? <div className='tagsVendorCust'> <Tag tone="success" > Vendor </Tag> </div> : log.loggable_type == "App\\Models\\InConnect\\Decorator" ? <div className='tagsDecoratorCust'> <Tag tone="success" > Decorator </Tag> </div> : ""
                                }</div>,
                                loggable_id: log.loggable_id,
                                description: log.description
                            }
                        );
                    });
                    setTableRows(my_rows);
                    setPagination({
                        path: result.data.path,
                        next_cursor: result.data.next_cursor,
                        next_page_url: result.data.next_page_url,
                        prev_cursor: result.data.prev_cursor,
                        prev_page_url: result.data.prev_page_url,
                    });
                }
                setLoading(false);
            })
            .catch((err) => {
                console.log(err);
                setLoading(false);
            });

    }, [reload])

    useEffect(() => {
        setReload(!reload);
    }, [selected, sortSelected]);

    const handleFiltersQueryChange = useCallback(
        (value) => {
            setQueryValue(value)
            clearTimeout(timeout)
            timeout = setTimeout(() => {
                setCurrentCursor(null);
                setReload(!reload);
            }, 500);
        },
        [tableRows]
    );

    const handleQueryValueRemove = useCallback(() => { setQueryValue(""); setCurrentCursor(null); setReload(!reload); }, [tableRows]);

    const handleFiltersClearAll = useCallback(() => {
        handleQueryValueRemove();
    }, [
        handleQueryValueRemove
    ]);

    const filters = [];

    const appliedFilters = [];

    const rowMarkup = tableRows.map(({ id, name, code, brand, category, subcategory, qty, unitcost, unitcostUSD, unitcostEGP, costofKGUSD, costofgmUSD, unitweightGR, unitcostIncludingweightUSD, unitcostIncludingweightEGP, grossmargin, finalprice },) => (
        <IndexTable.Row id={id} key={id}>

            <IndexTable.Cell>{id}</IndexTable.Cell>
            <IndexTable.Cell>{name}</IndexTable.Cell>
            <IndexTable.Cell>{code}</IndexTable.Cell>
            <IndexTable.Cell>{brand}</IndexTable.Cell>
            <IndexTable.Cell>{category}</IndexTable.Cell>
            <IndexTable.Cell>{subcategory}</IndexTable.Cell>
            <IndexTable.Cell>{qty}</IndexTable.Cell>
            <IndexTable.Cell>{unitcost}</IndexTable.Cell>
            <IndexTable.Cell>{unitcostUSD}</IndexTable.Cell>
            <IndexTable.Cell>{unitcostEGP}</IndexTable.Cell>
            <IndexTable.Cell>{costofKGUSD}</IndexTable.Cell>
            <IndexTable.Cell>{costofgmUSD}</IndexTable.Cell>
            <IndexTable.Cell>{unitweightGR}</IndexTable.Cell>
            <IndexTable.Cell>{unitcostIncludingweightUSD}</IndexTable.Cell>
            <IndexTable.Cell>{unitcostIncludingweightEGP}</IndexTable.Cell>
            <IndexTable.Cell>{grossmargin}</IndexTable.Cell>
            <IndexTable.Cell>{finalprice}</IndexTable.Cell>
        </IndexTable.Row>
    ));
    const [active, setActive] = useState(false);

    const [firstRun, setFirstRun] = useState(true);
    useEffect(() => {

        if (!firstRun) {
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
        }
        setFirstRun(false);

    }, [selected])


    // Modal popup
    const [activemodal, setActivemodal] = useState(false);

    const buttonRef = useRef(null);

    const handleOpenmodal = useCallback(() => setActivemodal(true), []);

    const handleClosemodal = useCallback(() => {
        setActivemodal(false);
    }, []);

    const activator = (''
        //   <div ref={buttonRef}>
        //     <Button onClick={handleOpenmodal}>Open</Button>
        //   </div>
    );
    // Modal popup



    // Modal data
    const [textFieldValuecostkg, setTextFieldValuecostkg] = useState('0.00');
    const handleTextFieldChangecostkg = useCallback(
        (valuecostkg) => setTextFieldValuecostkg(valuecostkg),
        [],
    );

    // second Gross Margin
    const [textFieldValuegrossmargin, setTextFieldValuegrossmargin] = useState('0');
    const handleTextFieldChangegrossmargin = useCallback(
        (valuegrossmargin) => setTextFieldValuegrossmargin(valuegrossmargin),
        [],
    );
    // Third
    const [textFieldValueblackmarket, setTextFieldValueblackmarket] = useState('0.00');
    const handleTextFieldChangeblackmarket = useCallback(
        (valueblackmarket) => setTextFieldValueblackmarket(valueblackmarket),
        [],
    );

    // Modal data
    //

    return (
        <div>
            <Grid>
                <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 12, lg: 12, xl: 12 }}>
                    <div style={{ display: "flex", justifyContent: 'space-between' }}>
                        <div style={{ display: "flex" }}>
                        </div>
                        <div className='orders-cc-in-printsave' style={{ display: "flex" }}>
                            <Select
                                labelInline
                                label="Rows:"
                                options={pageOptions}
                                value={pageCount}
                                onChange={handlePageCount}
                            />
                        </div>
                    </div>
                </Grid.Cell>
                <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 12, lg: 12, xl: 12 }}>
                    <Card>
                        <IndexFilters
                            sortOptions={sortOptions}
                            sortSelected={sortSelected}
                            queryValue={queryValue}
                            queryPlaceholder="Searching in all"
                            onQueryChange={handleFiltersQueryChange}
                            onQueryClear={handleQueryValueRemove}
                            onSort={setSortSelected}
                            cancelAction={{
                                onAction: onHandleCancel,
                                disabled: false,
                                loading: false,
                            }}
                            tabs={tabs}
                            selected={selected}
                            onSelect={setSelected}
                            canCreateNewView={false}
                            filters={filters}
                            appliedFilters={appliedFilters}
                            onClearAll={handleFiltersClearAll}
                            mode={mode}
                            setMode={setMode}
                            loading={loading}
                        />
                        <IndexTable
                            resourceName={resourceName}
                            itemCount={tableRows.length}
                            selectedItemsCount={
                                allResourcesSelected ? 'All' : selectedResources.length
                            }
                            onSelectionChange={handleSelectionChange}
                            headings={[
                                { title: 'id' },
                                { title: 'Name' },
                                { title: 'Code' },
                                { title: 'Brand' },
                                { title: 'Category' },
                                { title: 'Subcategory' },
                                { title: 'QTY' },
                                { title: 'Unit Cost EUR' },
                                { title: 'Unit Cost USD' },
                                { title: 'Unit Cost EGP' },
                                { title: 'Cost of KG (USD)' },
                                { title: 'Cost of Gram (USD)' },
                                { title: 'Unit Weight (GR)' },
                                { title: 'Unit Cost Including weight (USD)' },
                                { title: 'Unit Cost Including weight (EGP)' },

                                { title: 'Gross Margin' },
                                { title: 'Final Price (EGP)' },
                            ]}
                            hasMoreItems
                            selectable={false}
                            lastColumnSticky
                        >
                            {rowMarkup}

                        </IndexTable>

                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'flex-end' }}>
                            <Button variant="plain" onClick={handleOpenmodal}>
                                <span className='setting-color' style={{ display: 'flex', alignItems: 'center', justifyContent: 'flex-end' }}>
                                    <Icon
                                        source={SettingsFilledIcon}
                                        tone="base"
                                    />  <Text variant="headingMd" className="bg-inverse" as="h5">Settings</Text>
                                </span>
                            </Button>
                        </div>

                    </Card>
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', paddingTop: '22px', paddingBottom: '22px' }}>
                        <Pagination hasNext={pagination.next_cursor ? true : false} hasPrevious={pagination.prev_cursor ? true : false} onNext={() => {
                            setPagination({
                                ...pagination,
                                path: pagination.next_page_url
                            })
                            setCurrentCursor(pagination.next_cursor);
                            setReload(!reload);
                        }} onPrevious={() => {
                            setPagination({
                                ...pagination,
                                path: pagination.prev_page_url
                            })
                            setCurrentCursor(pagination.prev_cursor);
                            setReload(!reload);
                        }} />


                    </div>
                </Grid.Cell>
            </Grid>




            <Modal
                activator={buttonRef}
                open={activemodal}
                onClose={handleClosemodal}
                title={<Text variant="headingLg" as="h5">Pricing App Settings</Text>}
                primaryAction={{
                    content: 'Done',
                    onAction: handleClosemodal,
                }}
                secondaryActions={[
                    {
                        content: 'Cancel',
                        onAction: handleClosemodal,
                    },
                ]}
            >
                <Modal.Section>
                    <TextContainer>
                        <Text variant="headingLg" as="h5">Pricing Parameters</Text>
                        <Grid>
                            <Grid.Cell columnSpan={{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }}>
                                <TextField
                                    label={<Text variant="headingMd" as="h5">Cost of KG</Text>}
                                    //   type="number"
                                    value={textFieldValuecostkg}
                                    onChange={handleTextFieldChangecostkg}
                                    prefix="$"
                                    autoComplete="off"
                                />
                            </Grid.Cell>
                            <Grid.Cell columnSpan={{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }}>
                                <TextField
                                    label={<Text variant="headingMd" as="h5">Gross Margin</Text>}
                                    //   type="number"
                                    value={textFieldValuegrossmargin}
                                    onChange={handleTextFieldChangegrossmargin}
                                    suffix="%"
                                    autoComplete="off"
                                />
                            </Grid.Cell>
                            <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 6, lg: 12, xl: 12 }}>
                                <Text variant="headingLg" as="h5">Currency Parameters</Text>
                            </Grid.Cell>
                            <Grid.Cell columnSpan={{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }}>
                                <TextField
                                    label={<Text variant="headingMd" as="h5">Black Market EGP Markup</Text>}
                                    //   type="number"
                                    value={textFieldValueblackmarket}
                                    onChange={handleTextFieldChangeblackmarket}
                                    prefix="E£"
                                    suffix="%"
                                    autoComplete="off"
                                />
                            </Grid.Cell>
                            <Grid.Cell columnSpan={{ xs: 6, sm: 3, md: 3, lg: 6, xl: 6 }}>
                                {/* <TextField
                                    label="Final Black Market Price"
                                    //   type="number"
                                    value={textFieldValuegrossmargin}
                                    onChange={handleTextFieldChangegrossmargin}
                                    prefix="E£"
                                    autoComplete="off"
                                /> */}
                            </Grid.Cell>
                        </Grid>
                    </TextContainer>
                </Modal.Section>
            </Modal>


        </div>
    );
}

export default LogsTable;
