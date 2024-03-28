import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Box, Button } from '@mui/material';
import LogsTable from './Datatable';
import './Style.css';
import { useEffect } from 'react';

export default function Dashboard({ auth, user, pricingParameter, filter }) {

    return (
        <AuthenticatedLayout
            user={user}
        >
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">

                        <div className="p-6 text-gray-900 dark:text-gray-100">

                        <Box m={3} mt={0}>
                            <LogsTable pricingParameter={pricingParameter} filter={filter} />

                        </Box>
                        </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}
