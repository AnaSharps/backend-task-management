import React from "react";
import "../App.less";
import { Story, Meta } from "@storybook/react";

import { CustomInput, InputProps } from "../components/CustomInput";

export default {
	title: "Shared/CustomInput",
	component: CustomInput,
} as Meta;

const Template: Story<InputProps> = (args) => <CustomInput {...args} />;

export const Primary = Template.bind({});
Primary.args = {
	placeholder: "Sample Input",
};
