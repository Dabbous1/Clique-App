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
    useIndexResourceState, useSetIndexFiltersMode,
    Frame
} from '@shopify/polaris';
import queryString from 'query-string';
import { router, usePage } from '@inertiajs/react';
import { ArrowLeftIcon, ReceiptDollarFilledIcon, RefreshIcon, SettingsFilledIcon } from "@shopify/polaris-icons";
import { useCallback, useEffect, useRef, useState } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import { CalendarViewMonthSharp, Sync } from '@mui/icons-material';
import toast, { Toaster } from 'react-hot-toast';
import { confirmAlert } from "react-confirm-alert";
import "react-confirm-alert/src/react-confirm-alert.css";
function LogsTable({ filter , pricingParameter}) {
    const page = usePage().props;
    const { query } = page.ziggy;
    let timeout = null;
    const resourceName = {
        singular: 'Product',
        plural: 'Products',
    };
    const pageOptions = [
        { label: '5', value: '5' },
        { label: '10', value: '10' },
        { label: '20', value: '20' },
        { label: '50', value: '50' },
        { label: '100', value: '100' },
    ];
    const [pageCount, setPageCount] = useState("10");
    const [tableRows, setTableRows] = useState([]);
    const capitalize =(str)=>{
        return str.charAt(0).toUpperCase() + str.slice(1);
        }
    const formatCurrency = (amount, currency) => (
        <span style={{ fontSize: '14px' }}>{currencyFormat(parseFloat(amount))} <b>{currency}</b></span>
    );
    const currencyFormat = (num) => {
        return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
     }
    const showStatus = (status) => <b style={{ fontWeight: '900', fontSize: '14px', color: status === 'active' ? 'green' : status === 'draft' ? 'red' : 'blue' }}>{capitalize(status)}</b>;
    const [selected, setSelected] = useState(0);
    const [itemStrings, setItemStrings] = useState([
        'All',
        'Active',
        'Draft',
        'Archived'
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
        { label: 'Id', value: 'id ASC', directionLabel: 'Ascending' },
        { label: 'Id', value: 'id DESC', directionLabel: 'Descending' },
    ];
    const [sortSelected, setSortSelected] = useState(['id ASC']);
    const [queryValue, setQueryValue] = useState("");
    const { mode, setMode } = useSetIndexFiltersMode();
    const onHandleCancel = () => { };
    const [pagination, setPagination] = useState({
        path: route("products.list"),
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
    const handleRefresh = () => {
        setPagination({
            path: route("products.list"),
            next_cursor: null,
            next_page_url: null,
            prev_cursor: null,
            prev_page_url: null,
        })
        setPageCount("10");
        setCurrentCursor(null);
        setSortSelected(['id ASC'])
        setQueryValue('')
        setSelected(0)
        setReload(!reload);
    }
    useEffect(() => {
        let url = new URL(pagination.path);
        url.searchParams.set('embedded', query.embedded);
        url.searchParams.set('host', query.host);
        url.searchParams.set('id_token', query.id_token);
        url.searchParams.set('shop', query.shop);
        url.searchParams.set('token', query.token);
        url.searchParams.set('page_count', pageCount);
        if (currentCursor) {
            url.searchParams.set('cursor', currentCursor);
        }
        if (filter.trace == 'Active') {
            setSelected(1)
        }
        if (filter.trace == 'Draft') {
            setSelected(2)
        }
        if (filter.trace == 'Archived') {
            setSelected(3)
        }
        if (selected == 0) {
            url.searchParams.delete('trace')
        }
        else if (selected == 1) {
            url.searchParams.set('trace', 'Active')
        }
        else if (selected == 2) {
            url.searchParams.set('trace', 'Draft')
        }
        else if (selected == 3) {
            url.searchParams.set('trace', 'Archived')
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
                    result.data.data.forEach((product, index) => {
                        console.log(product)
                        my_rows.push(
                            {
                                id: product.id,
                                name: product.name,
                                code: product.variants[0].code,
                                brand: product.brand,
                                category: product.category,
                                qty: product.count,
                                unitcost: formatCurrency(product.variants[0].unit_cost_eur, 'EUR'),
                                unitcostUSD: formatCurrency(product.variants[0].unit_cost_usd, 'USD'),
                                unitcostEGP: formatCurrency(product.variants[0].unit_cost_egp, 'EGP'),
                                costofKGUSD: formatCurrency(pricingParameter?.cost_of_kg, 'USD'),
                                costofgmUSD: formatCurrency(product.variants[0].cost_of_gram_usd, 'USD'),
                                unitweightGR: formatCurrency(product.variants[0].unit_weight_gram, 'gm'),
                                unitcostIncludingweightUSD: formatCurrency(product.variants[0].unit_cost_with_weight_cost_usd, 'USD'),
                                unitcostIncludingweightEGP: formatCurrency(product.variants[0].unit_cost_with_weight_cost_egp, 'EGP'),
                                grossmargin: formatCurrency(pricingParameter?.gross_margin, '%'),
                                finalprice: <b>{formatCurrency(product.variants[0].final_price_egp, 'EGP')}</b>,
                                status: showStatus(product.status),
                                variants_count: product.variants_count
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
    const rowMarkup = tableRows.map(({ id, name, code, status, brand, category, qty, unitcost, unitcostUSD, unitcostEGP, costofKGUSD, costofgmUSD, unitweightGR, unitcostIncludingweightUSD, unitcostIncludingweightEGP, grossmargin, finalprice,variants_count },) => (
        <IndexTable.Row id={id} key={id}>
            <IndexTable.Cell>{id}</IndexTable.Cell>
            <IndexTable.Cell>{name}</IndexTable.Cell>
            <IndexTable.Cell>{code}</IndexTable.Cell>
            <IndexTable.Cell>{status}</IndexTable.Cell>
            <IndexTable.Cell>{variants_count}</IndexTable.Cell>
            <IndexTable.Cell>{brand}</IndexTable.Cell>
            <IndexTable.Cell>{category}</IndexTable.Cell>
            <IndexTable.Cell>{qty}</IndexTable.Cell>
            <IndexTable.Cell>{unitcost}</IndexTable.Cell>
            <IndexTable.Cell>{unitcostUSD}</IndexTable.Cell>
            <IndexTable.Cell>{unitcostEGP}</IndexTable.Cell>
            <IndexTable.Cell>{costofKGUSD}</IndexTable.Cell>
            <IndexTable.Cell>{costofgmUSD}</IndexTable.Cell>
            <IndexTable.Cell>{unitweightGR}</IndexTable.Cell>
            <IndexTable.Cell>{unitcostIncludingweightUSD}</IndexTable.Cell>
            <IndexTable.Cell>{unitcostIncludingweightEGP}</IndexTable.Cell>
            <IndexTable.Cell>{grossmargin?grossmargin:0}</IndexTable.Cell>
            <IndexTable.Cell>{finalprice}</IndexTable.Cell>
        </IndexTable.Row>
    ));
    const [firstRun, setFirstRun] = useState(true);
    useEffect(() => {
        if (!firstRun) {
            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
        }
        setFirstRun(false);
    }, [selected])

    const confirmSync = async() => {
        const promise = new Promise((resolve, reject) => {
            setTimeout(async () => {
                await fetch(route('sync-produccts', query), {
                    method: 'GET',
                })
                    .then(async (response) => {
                        var results = await response.json()
                        setActivemodal(false)
                        resolve(results);
                    })
                    .catch(reject);
            }, 2000);
        });
        toast.promise(
            promise,
            {
                loading: 'Updating.......',
                success: (data) => `Successfully ${data.message}`,
                error: (err) => `This just happened: ${err.toString()}`,
            },
            {
                style: {
                    minWidth: '250px',
                },
                success: {
                    duration: 5000,
                },
                error: {
                    duration: 5000,
                },
            }
        ).then(() => {
            handleRefresh()
        }).catch((error) => {
            console.error("An error occurred:", error);
        });
    }
    const handleSync = () => {
        console.log('confirm')
        confirmAlert({
            title: "Confirm to Sync",
            message: "Are you sure you want to do this?",
            buttons: [
              {
                label: "Yes",
                onClick: () => confirmSync()
              },
              {
                label: "No"
              }
            ]
        });
    }

    // Modal popup
    const [activemodal, setActivemodal] = useState(false);
    const buttonRef = useRef(null);
    const handleOpenmodal = useCallback(() => setActivemodal(true), []);
    const handleClosemodal = useCallback(() => {
        setActivemodal(false);
    }, []);
    // Modal data
    const [textFieldValuecostkg, setTextFieldValuecostkg] = useState(pricingParameter?.cost_of_kg);
    const handleTextFieldChangecostkg = useCallback(
        (valuecostkg) => setTextFieldValuecostkg(valuecostkg),
        [],
    );
    const handleFormSubmit =  () => {
        const data = {
            cost_of_kg: textFieldValuecostkg,
            gross_margin: textFieldValuegrossmargin,
            bm_egp_markup: textFieldValueblackmarket
        };
        const promise = new Promise((resolve, reject) => {
            setTimeout(async () => {
                await fetch(route('submit-pricing', query), {
                    method: 'POST',
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(data),
                })
                    .then(async (response) => {
                        var results = await response.json()
                        setActivemodal(false)
                        resolve(results);
                    })
                    .catch(reject);
            }, 2000);
        });
        toast.promise(
            promise,
            {
                loading: 'Updating.......',
                success: (data) => `Successfully ${data.message}`,
                error: (err) => `This just happened: ${err.toString()}`,
            },
            {
                style: {
                    minWidth: '250px',
                },
                success: {
                    duration: 5000,
                },
                error: {
                    duration: 5000,
                },
            }
        ).then(() => {
            handleRefresh()
        })
            .catch((error) => {
                console.error("An error occurred:", error);
            });
    }
    // second Gross Margin
    const [textFieldValuegrossmargin, setTextFieldValuegrossmargin] = useState(pricingParameter?.gross_margin);
    const handleTextFieldChangegrossmargin = useCallback(
        (valuegrossmargin) => setTextFieldValuegrossmargin(valuegrossmargin),
        [],
    );
    // Third
    const [textFieldValueblackmarket, setTextFieldValueblackmarket] = useState(pricingParameter?.bm_egp_markup?pricingParameter?.bm_egp_markup:0);
    const handleTextFieldChangeblackmarket = useCallback(
        (valueblackmarket) => setTextFieldValueblackmarket(valueblackmarket),
        [],
    );
    // Modal data
    //
    return (
        <div>
            <Toaster position="top-right" reverseOrder={false} />
            <Grid>
                <Grid.Cell columnSpan={{ xs: 6, sm: 6, md: 12, lg: 12, xl: 12 }}>
                    <div style={{ display: "flex", justifyContent: 'end' }}>
                        <div style={{ display: "flex", marginRight: '1rem' }}>
                            <Button onClick={handleRefresh} className="session-token" icon={RefreshIcon}>
                                Refresh
                            </Button>
                        </div>
                        <div style={{ display: "flex", marginRight: '1rem' }}>
                            <Button onClick={handleSync} className="session-token" icon={RefreshIcon}>
                                Sync
                            </Button>
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
                                { title: 'Status' },
                                { title: 'Variants Count' },
                                { title: 'Brand' },
                                { title: 'Category' },
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
                    onAction: handleFormSubmit,
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
