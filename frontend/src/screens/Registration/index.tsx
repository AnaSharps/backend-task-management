import React from "react";
// import { Formik } from "formik";
import styles from "./style.module.css";
import { CustomInput } from "../../components/CustomInput";
// import { useAppSelector, useAppDispatch } from "../../app/hooks";
import { CustomButton } from "../../components/Button";
import { LabelledFormComponent } from "../../components/LabelledFormComponent";

export interface RegisterProps {}

export const Register: React.FC<RegisterProps> = () => {
	return (
		<div className={styles.mainContainer}>
			<span className={styles.header}>Complete your Registration!</span>
			<div className={styles.container}>
				<form className={styles.formWrapper}>
					<div className={styles.formContainer}>
						<LabelledFormComponent labelText="Email">
							<CustomInput
								type="text"
								containerStyle={{ margin: "5px 0px" }}
								backgroundColor="#ededed"
								placeholder="Name"
								className={styles.email}
								// value={values.email}
								name="username"
								// onChange={handleChange}
								// onBlur={handleBlur}
							/>
						</LabelledFormComponent>
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
						<LabelledFormComponent labelText="Confirm Password">
							<CustomInput
								backgroundColor="#ededed"
								containerStyle={{ margin: "5px 0px" }}
								placeholder="Confirm Password"
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
								name="confirm-password"
								// onChange={handleChange}
								// onBlur={handleBlur}
							/>
						</LabelledFormComponent>
						<CustomButton
							className={styles.button}
							type="primary"
							text="CONFIRM"
							loading={false}
							onClick={() => console.log("Registered!")}
						/>
					</div>
				</form>
			</div>
		</div>
	);
};
