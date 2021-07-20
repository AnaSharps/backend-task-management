/* eslint-disable jsx-a11y/label-has-associated-control */
import { Typography } from "antd";
import React, { CSSProperties, ReactNode } from "react";
import styles from "./style.module.css";

const { Text } = Typography;
export interface LabelledFormComponentProps {
	labelText: string;
	formComponent?: ReactNode;
	children?: ReactNode;
	width?: string;
	htmlFor?: string;
	style?: CSSProperties;
	error?: boolean;
	errorText?: string;
}

export const LabelledFormComponent: React.FC<LabelledFormComponentProps> = ({
	labelText,
	htmlFor,
	formComponent,
	children,
	style = {},
	width = "",
	error = false,
	errorText = "",
}) => {
	return (
		<div className={styles.formComponent} style={{ width, ...style }}>
			<label htmlFor={htmlFor}>{labelText}</label>
			{formComponent || children}
			{error && (
				<Text style={{ fontSize: "12px" }} type="danger">
					{errorText}
				</Text>
			)}
		</div>
	);
};
