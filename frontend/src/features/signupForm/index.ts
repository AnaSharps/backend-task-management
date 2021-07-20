import { createSlice } from "@reduxjs/toolkit";
import { RootState } from "../../app/store";

export interface SignUpState {
	showSignup: boolean;
}

const initialState: SignUpState = {
	showSignup: false,
};

export const signupSlice = createSlice({
	name: "signup",
	initialState,
	reducers: {
		changeSignup: (state) => {
			state.showSignup = !state.showSignup;
		},
	},
});

export const { changeSignup } = signupSlice.actions;

export const selectSignup = (state: RootState) => state.signup.showSignup;

export default signupSlice.reducer;
