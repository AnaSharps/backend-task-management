import React from "react";
import { Formik } from "formik";
import styles from "./style.module.css";
import { CustomInput } from "../../components/CustomInput";
import { useAppSelector, useAppDispatch } from "../../app/hooks";
import { CustomButton } from "../../components/Button";
import { LabelledFormComponent } from "../../components/LabelledFormComponent";
import { changeSignup, selectSignup } from "../../features/signupForm";
export interface LoginProps {}

export const Login: React.FC<LoginProps> = () => {
	const signup = useAppSelector(selectSignup);
	const dispatch = useAppDispatch();

	return (
		<div className={styles.mainContainer}>
			<span className={styles.header}>LOGIN</span>
			<div className={styles.container}>
				<form className={styles.formWrapper}>
					<div className={styles.formContainer}>
						<LabelledFormComponent labelText="Email">
							<CustomInput
								type="email"
								containerStyle={{ margin: "5px 0px" }}
								backgroundColor="#ededed"
								placeholder="Email"
								className={styles.email}
								// value={values.email}
								name="email"
								// onChange={handleChange}
								// onBlur={handleBlur}
							/>
						</LabelledFormComponent>
						{!signup && (
							<LabelledFormComponent labelText="Password">
								<CustomInput
									backgroundColor="#ededed"
									containerStyle={{ margin: "5px 0px" }}
									placeholder="Password"
									type="password"
									// type={passVisible ? "text" : "password"}
									className={styles.passwordInput}
									// suffix={
									//   passVisible ? (
									//     <AiOutlineEye
									//       fontSize={24}
									//       onClick={() => handleEye(false)}
									//     />
									//   ) : (
									//     <AiOutlineEyeInvisible
									//       fontSize={24}
									//       onClick={() => handleEye(true)}
									//     />
									//   )
									// }
									// value={values.password}
									name="password"
									// onChange={handleChange}
									// onBlur={handleBlur}
								/>
							</LabelledFormComponent>
						)}
						<CustomButton
							className={styles.button}
							type="primary"
							text={signup ? "Confirm Email" : "Login"}
							loading={false}
							onClick={() => console.log("LoggedIn")}
						/>

						<CustomButton
							isSecondary={true}
							text={signup ? "LOGIN" : "Register"}
							onClick={() => dispatch(changeSignup())}
						/>
					</div>
				</form>
			</div>
		</div>
	);
};
