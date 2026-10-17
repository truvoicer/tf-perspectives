// packages/truvoicer/tf-perspectives/resources/js/pages/auth.tsx
import { useForm } from '@inertiajs/react';
import { route } from 'ziggy-js';
import type { FormEvent } from 'react';
import { Row, Col, Form, Button, InputGroup } from 'react-bootstrap';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {
    faEnvelope,
    faLock,
    faUser,
    faShoePrints,
    faEye,
    faEyeSlash,
} from '@fortawesome/free-solid-svg-icons';
import { useState } from 'react';
import Layout from '../components/perspectives/layout';

interface AuthProps {
    mode: 'login' | 'register';
    canResetPassword?: boolean;
    status?: string;
}

interface AuthForm {
    first_name: string;
    last_name: string;
    email: string;
    password: string;
    password_confirmation: string;
    remember: boolean;
    [key: string]: string | boolean;
}

export default function Auth({ mode, status }: AuthProps) {
    const isRegister = mode === 'register';
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm<AuthForm>({
        first_name: '',
        last_name: '',
        email: '',
        password: '',
        password_confirmation: '',
        remember: false,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(isRegister ? '/perspectives/register' : '/perspectives/login', {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <Layout>
            <Row className="justify-content-center">
                <Col md={8} lg={6} xl={5}>
                    <div className="tf-auth-card">
                        <div className="tf-auth-icon">
                            <FontAwesomeIcon icon={faShoePrints} />
                        </div>

                        <h1>{isRegister ? 'Join the circle' : 'Welcome back'}</h1>
                        <p className="text-muted">
                            {isRegister
                                ? 'Share your view. Step into other people’s.'
                                : 'Sign in to keep the conversation going.'}
                        </p>

                        {status && (
                            <div className="alert alert-info small">{status}</div>
                        )}

                        <Form onSubmit={submit}>
                            {isRegister && (
                                <Row className="g-2">
                                    <Col md={6}>
                                        <Form.Group className="mb-3">
                                            <Form.Label>First name</Form.Label>
                                            <InputGroup>
                                                <InputGroup.Text>
                                                    <FontAwesomeIcon icon={faUser} />
                                                </InputGroup.Text>
                                                <Form.Control
                                                    value={data.first_name}
                                                    onChange={(e) =>
                                                        setData('first_name', e.target.value)
                                                    }
                                                    required
                                                    maxLength={60}
                                                    autoComplete="given-name"
                                                    isInvalid={Boolean(errors.first_name)}
                                                />
                                                <Form.Control.Feedback type="invalid">
                                                    {errors.first_name}
                                                </Form.Control.Feedback>
                                            </InputGroup>
                                        </Form.Group>
                                    </Col>

                                    <Col md={6}>
                                        <Form.Group className="mb-3">
                                            <Form.Label>Last name</Form.Label>
                                            <InputGroup>
                                                <InputGroup.Text>
                                                    <FontAwesomeIcon icon={faUser} />
                                                </InputGroup.Text>
                                                <Form.Control
                                                    value={data.last_name}
                                                    onChange={(e) =>
                                                        setData('last_name', e.target.value)
                                                    }
                                                    required
                                                    maxLength={60}
                                                    autoComplete="family-name"
                                                    isInvalid={Boolean(errors.last_name)}
                                                />
                                                <Form.Control.Feedback type="invalid">
                                                    {errors.last_name}
                                                </Form.Control.Feedback>
                                            </InputGroup>
                                        </Form.Group>
                                    </Col>
                                </Row>
                            )}

                            <Form.Group className="mb-3">
                                <Form.Label>Email</Form.Label>
                                <InputGroup>
                                    <InputGroup.Text>
                                        <FontAwesomeIcon icon={faEnvelope} />
                                    </InputGroup.Text>
                                    <Form.Control
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        required
                                        autoComplete="email"
                                        isInvalid={Boolean(errors.email)}
                                    />
                                    <Form.Control.Feedback type="invalid">
                                        {errors.email}
                                    </Form.Control.Feedback>
                                </InputGroup>
                            </Form.Group>

                            <Form.Group className="mb-3">
                                <Form.Label>Password</Form.Label>
                                <InputGroup>
                                    <InputGroup.Text>
                                        <FontAwesomeIcon icon={faLock} />
                                    </InputGroup.Text>
                                    <Form.Control
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        required
                                        minLength={8}
                                        autoComplete={
                                            isRegister ? 'new-password' : 'current-password'
                                        }
                                        isInvalid={Boolean(errors.password)}
                                    />
                                    <Button
                                        variant="outline-secondary"
                                        type="button"
                                        onClick={() => setShowPassword((v) => !v)}
                                        tabIndex={-1}
                                        title={showPassword ? 'Hide password' : 'Show password'}
                                    >
                                        <FontAwesomeIcon icon={showPassword ? faEyeSlash : faEye} />
                                    </Button>
                                    <Form.Control.Feedback type="invalid">
                                        {errors.password}
                                    </Form.Control.Feedback>
                                </InputGroup>
                            </Form.Group>

                            {isRegister && (
                                <Form.Group className="mb-3">
                                    <Form.Label>Confirm password</Form.Label>
                                    <InputGroup>
                                        <InputGroup.Text>
                                            <FontAwesomeIcon icon={faLock} />
                                        </InputGroup.Text>
                                        <Form.Control
                                            type={showPassword ? 'text' : 'password'}
                                            value={data.password_confirmation}
                                            onChange={(e) =>
                                                setData('password_confirmation', e.target.value)
                                            }
                                            required
                                            minLength={8}
                                            autoComplete="new-password"
                                            isInvalid={Boolean(errors.password_confirmation)}
                                        />
                                        <Form.Control.Feedback type="invalid">
                                            {errors.password_confirmation}
                                        </Form.Control.Feedback>
                                    </InputGroup>
                                </Form.Group>
                            )}

                            {!isRegister && (
                                <Form.Group className="mb-3">
                                    <Form.Check
                                        type="checkbox"
                                        id="remember"
                                        label="Keep me signed in"
                                        checked={data.remember}
                                        onChange={(e) =>
                                            setData('remember', e.target.checked)
                                        }
                                    />
                                </Form.Group>
                            )}

                            {errors.email && !isRegister && (
                                <div className="alert alert-danger small py-2">
                                    {errors.email}
                                </div>
                            )}

                            <Button
                                type="submit"
                                variant="primary"
                                size="lg"
                                className="w-100"
                                disabled={processing}
                            >
                                {processing
                                    ? 'One moment…'
                                    : isRegister
                                      ? 'Join'
                                      : 'Sign in'}
                            </Button>
                        </Form>

                        <div className="tf-auth-foot">
                            {isRegister ? (
                                <>
                                    Already have an account?{' '}
                                    <a href={route('perspectives.login')}>Sign in</a>
                                </>
                            ) : (
                                <>
                                    New here? <a href={route('perspectives.register')}>Create an account</a>
                                </>
                            )}
                        </div>
                    </div>
                </Col>
            </Row>
        </Layout>
    );
}
