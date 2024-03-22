import { usePage } from "@inertiajs/react";

const EmbeddedApp = () => {

    const page = usePage().props;
    console.log(page);

    return (
        <div>
            Embedded App
        </div>
    );
}

export default EmbeddedApp;
