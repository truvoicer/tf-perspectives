import { useForm, usePage } from '@inertiajs/react';
import { Button, Form } from 'react-bootstrap';
import Layout from '../components/perspectives/layout';
import { route } from 'ziggy-js';

export default function Account() {
    const { props } = usePage();
    const { data, setData, put, processing, errors, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('perspectives.account.password'), {
            onSuccess: () => reset(),
        });
    };

    return (
        <Layout>
            <div className="tf-panel mx-auto" style={{ maxWidth: '600px' }}>
                <h1 className="tf-page-title mb-4">Account Settings</h1>

                {props.flash?.success && (
                    <div className="alert alert-success">{props.flash.success as string}</div>
                )}

                <h3 className="h5 mb-3">Update Password</h3>
                <Form onSubmit={updatePassword}>
                    <Form.Group className="mb-3">
                        <Form.Label>Current Password</Form.Label>
                        <Form.Control
                            type="password"
                            value={data.current_password}
                            onChange={(e) => setData('current_password', e.target.value)}
                            isInvalid={!!errors.current_password}
                        />
                        <Form.Control.Feedback type="invalid">{errors.current_password}</Form.Control.Feedback>
                    </Form.Group>

                    <Form.Group className="mb-3">
                        <Form.Label>New Password</Form.Label>
                        <Form.Control
                            type="password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            isInvalid={!!errors.password}
                        />
                        <Form.Control.Feedback type="invalid">{errors.password}</Form.Control.Feedback>
                    </Form.Group>

                    <Form.Group className="mb-4">
                        <Form.Label>Confirm New Password</Form.Label>
                        <Form.Control
                            type="password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            isInvalid={!!errors.password_confirmation}
                        />
                    </Form.Group>

                    <Button variant="primary" type="submit" disabled={processing}>
                        Update Password
                    </Button>
                </Form>
            </div>
        </Layout>
    );
}
