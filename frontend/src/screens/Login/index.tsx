import React, { useState } from "react";
// import { Formik } from "formik";
import styles from "./style.module.css";
import { CustomInput } from "../../components/CustomInput";
import { useAppSelector, useAppDispatch } from "../../app/hooks";
import { CustomButton } from "../../components/Button";
import { LabelledFormComponent } from "../../components/LabelledFormComponent";
import { changeSignup, selectSignup } from "../../features/signupForm";
import { sendVerify, selectVerifySent } from "../../features/verifyEmailSent";
import { useHistory } from "react-router-dom";
import axios from "axios";
import { host } from "../../app/constants";

// axios.defaults.headers.common["Access-Control-Allow-Origin"] = "*";

export interface LoginProps {}

export const Login: React.FC<LoginProps> = () => {
	const signup = useAppSelector(selectSignup);
	const verifyEmail = useAppSelector(selectVerifySent);
	const dispatch = useAppDispatch();

	const [email, setEmail] = useState("");
	const [emailErr, setEmailErr] = useState("");
	const [passErr, setPassErr] = useState("");
	const [pass, setPass] = useState("");

	let history = useHistory();

	function handleChange(val: string, type: "email" | "pass") {
		if (type === "email") {
			// const valid = validate(val, "email");
			// setEmailErr(valid);
			setEmail(val);
		} else {
			// const valid = validate(val, "pass");
			// setPassErr(valid);
			setPass(val);
		}
	}

	function handleSubmit(authType: "login" | "register") {
		if (authType === "login") {
			axios
				.post(`${host}/login`, {
					email,
					password: pass,
				})
				.then((res) => {
					console.log(res.data.token);
				})
				.catch((err) => console.error(err));
		} else {
		}
	}

	return (
		<div className={styles.mainContainer}>
			<span className={styles.header}>{signup ? "REGISTER" : "LOGIN"}</span>
			<div className={styles.container}>
				<form className={styles.formWrapper}>
					<div className={styles.formContainer}>
						<LabelledFormComponent labelText="Email">
							{emailErr !== "" && <div className={styles.err}>{emailErr}</div>}
							<CustomInput
								type="email"
								containerStyle={{ margin: "5px 0px" }}
								backgroundColor="#ededed"
								placeholder="Email"
								className={styles.email}
								name="email"
								value={email}
								onChange={(e) => handleChange(e.target.value, "email")}
							/>
						</LabelledFormComponent>
						{!signup && (
							<LabelledFormComponent labelText="Password">
								{passErr !== "" && <div className={styles.err}>{passErr}</div>}
								<CustomInput
									backgroundColor="#ededed"
									containerStyle={{ margin: "5px 0px" }}
									placeholder="Password"
									type="password"
									className={styles.passwordInput}
									name="password"
									value={pass}
									onChange={(e) => handleChange(e.target.value, "pass")}
								/>
							</LabelledFormComponent>
						)}
						{!signup && (
							<CustomButton
								isSecondary={true}
								size="small"
								text="Forgot Password?"
								onClick={() => console.log("Forgot Password")}
							/>
						)}
						{signup && verifyEmail && (
							<CustomButton
								isSecondary={true}
								size="small"
								text="Resend Verification Link"
								onClick={() => console.log("Forgot Password")}
							/>
						)}
						<CustomButton
							className={styles.button}
							type="primary"
							text={signup ? "Confirm Email" : "Login"}
							loading={false}
							onClick={() =>
								signup ? handleSubmit("register") : handleSubmit("login")
							}
						/>
						<div className={styles.changeView}>
							{signup ? "Already a user?" : "New to Task Manager?"}
							<CustomButton
								isSecondary={true}
								size="small"
								text={signup ? "Login" : "Register"}
								onClick={() => dispatch(changeSignup())}
							/>
						</div>
					</div>
				</form>
			</div>
		</div>
	);
};
