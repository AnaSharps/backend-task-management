import { Input, InputProps as AntInputProps } from "antd";
import { SizeType } from "antd/lib/config-provider/SizeContext";
import React, { CSSProperties } from "react";
import classnames from "classnames";
import styles from "./style.module.css";

const { TextArea } = Input;

export interface InputProps extends AntInputProps {
	placeholder?: string;
	size?: SizeType;
	backgroundColor?: string;
	bordered?: boolean;
	className?: string;
	width?: string;
	multiline?: boolean;
	rows?: number;
	containerStyle?: CSSProperties;
	onMultilineChange?: any;
}

export const CustomInput: React.FC<InputProps> = ({
	placeholder = "",
	size = "large",
	backgroundColor = "rgba(0,0,0,0)",
	className = "",
	bordered = false,
	width = "100%",
	style,
	containerStyle,
	rows = 2,
	multiline = false,
	onMultilineChange,
	...props
}) => {
	return (
		<div className={styles.inputContainer} style={containerStyle}>
			{multiline ? (
				<TextArea
					autoSize={{ minRows: rows, maxRows: 5 }}
					rows={5}
					placeholder={placeholder}
					className={classnames(styles.input, className)}
					bordered={bordered}
					style={{ backgroundColor, width, ...style }}
					defaultValue={props.defaultValue}
					onChange={onMultilineChange}
				/>
			) : (
				<Input
					placeholder={placeholder}
					className={classnames(styles.input, className)}
					size={size}
					bordered={bordered}
					style={{ backgroundColor, width, ...style }}
					// eslint-disable-next-line react/jsx-props-no-spreading
					{...props}
				/>
			)}
		</div>
	);
};
